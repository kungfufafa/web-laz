<?php

namespace App\Services;

use App\Models\PpobTransaction;
use App\Models\ProviderCallbackLog;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class PpobCallbackService
{
    public function __construct(
        private readonly MidtransClient $midtransClient,
        private readonly TripayClient $tripayClient,
        private readonly DigiflazzClient $digiflazzClient,
        private readonly DigiflazzPpobService $digiflazzPpobService,
        private readonly PpobTransactionService $transactionService,
        private readonly PpobAlertService $alertService,
    ) {}

    public function handleMidtransCallback(Request $request): array
    {
        $rawBody = $request->getContent();
        $payload = json_decode($rawBody, true);
        $signature = is_array($payload) ? (string) ($payload['signature_key'] ?? '') : null;
        $orderId = is_array($payload) ? (string) ($payload['order_id'] ?? '') : '';

        $log = ProviderCallbackLog::query()->create([
            'provider' => 'midtrans',
            'event' => is_array($payload) ? (string) ($payload['transaction_status'] ?? '') : null,
            'signature' => $signature,
            'headers' => $request->headers->all(),
            'payload' => is_array($payload) ? $payload : null,
            'is_valid_signature' => is_array($payload) && $this->midtransClient->verifyNotificationSignature($payload),
        ]);

        if (! $log->is_valid_signature) {
            $this->alertService->logSecurityWarning('Invalid Midtrans callback signature received.', [
                'order_id' => $orderId,
                'signature' => $signature,
            ]);
            $log->forceFill(['processing_result' => 'invalid_signature'])->save();

            return [
                'status' => 401,
                'body' => ['success' => false, 'message' => 'Invalid signature.'],
            ];
        }

        if (! is_array($payload) || $orderId === '') {
            $log->forceFill(['processing_result' => 'invalid_payload'])->save();

            return [
                'status' => 422,
                'body' => ['success' => false, 'message' => 'Invalid callback payload.'],
            ];
        }

        /** @var PpobTransaction|null $transaction */
        $transaction = PpobTransaction::query()
            ->where('midtrans_order_id', $orderId)
            ->first();

        if (! $transaction) {
            $this->alertService->logWarning('Midtrans callback references unknown transaction.', [
                'order_id' => $orderId,
                'transaction_id' => Arr::get($payload, 'transaction_id'),
            ]);
            $log->forceFill([
                'external_id' => $orderId,
                'processing_result' => 'transaction_not_found',
            ])->save();

            return [
                'status' => 404,
                'body' => ['success' => false, 'message' => 'Transaction not found.'],
            ];
        }

        $statusPayload = $payload;

        try {
            $latestPayload = $this->midtransClient->getTransactionStatus($orderId);

            if ($latestPayload !== []) {
                $statusPayload = $latestPayload;
            }
        } catch (\Throwable $exception) {
            $this->alertService->logWarning('Failed to pull Midtrans transaction status after callback.', [
                'order_id' => $orderId,
                'error' => $exception->getMessage(),
            ]);
        }

        $this->transactionService->syncMidtransPayload($transaction, $statusPayload);

        if ($transaction->shouldDispatchFulfillment()) {
            $this->transactionService->dispatchFulfillment($transaction->uuid);
        }

        $log->forceFill([
            'external_id' => (string) ($transaction->midtrans_transaction_id ?? $transaction->midtrans_order_id ?? $orderId),
            'processing_result' => 'processed',
            'processed_at' => now(),
        ])->save();

        return [
            'status' => 200,
            'body' => ['success' => true],
        ];
    }

    public function handleTripayCallback(Request $request): array
    {
        $rawBody = $request->getContent();
        $signature = $request->header('X-Callback-Signature');
        $event = $request->header('X-Callback-Event');
        $payload = json_decode($rawBody, true);

        $log = ProviderCallbackLog::query()->create([
            'provider' => 'tripay',
            'event' => is_string($event) ? $event : null,
            'signature' => is_string($signature) ? $signature : null,
            'headers' => $request->headers->all(),
            'payload' => is_array($payload) ? $payload : null,
            'is_valid_signature' => $this->tripayClient->verifyCallbackSignature($rawBody, $signature),
        ]);

        if (! $log->is_valid_signature) {
            $this->alertService->logSecurityWarning('Invalid Tripay callback signature received.', [
                'event' => $event,
                'signature' => $signature,
            ]);
            $log->forceFill(['processing_result' => 'invalid_signature'])->save();

            return [
                'status' => 401,
                'body' => ['success' => false, 'message' => 'Invalid signature.'],
            ];
        }

        if ($event !== 'payment_status' || ! is_array($payload)) {
            $log->forceFill(['processing_result' => 'invalid_payload'])->save();

            return [
                'status' => 422,
                'body' => ['success' => false, 'message' => 'Invalid callback payload.'],
            ];
        }

        /** @var PpobTransaction|null $transaction */
        $transaction = PpobTransaction::query()
            ->whereNull('midtrans_order_id')
            ->where(function ($query) use ($payload): void {
                $query
                    ->where('payment_gateway_order_id', (string) ($payload['merchant_ref'] ?? ''))
                    ->orWhere('payment_gateway_reference', (string) ($payload['reference'] ?? ''));
            })
            ->first();

        if (! $transaction) {
            $this->alertService->logWarning('Tripay callback references unknown transaction.', [
                'reference' => Arr::get($payload, 'reference'),
                'merchant_ref' => Arr::get($payload, 'merchant_ref'),
            ]);
            $log->forceFill([
                'external_id' => Arr::get($payload, 'reference'),
                'processing_result' => 'transaction_not_found',
            ])->save();

            return [
                'status' => 404,
                'body' => ['success' => false, 'message' => 'Transaction not found.'],
            ];
        }

        $this->transactionService->syncTripayPayload($transaction, $payload);

        if ($transaction->shouldDispatchFulfillment()) {
            $this->transactionService->dispatchFulfillment($transaction->uuid);
        }

        $log->forceFill([
            'external_id' => $transaction->payment_gateway_reference,
            'processing_result' => 'processed',
            'processed_at' => now(),
        ])->save();

        return [
            'status' => 200,
            'body' => ['success' => true],
        ];
    }

    public function handleDigiflazzCallback(Request $request): array
    {
        $rawBody = $request->getContent();
        $signature = $request->header('X-Hub-Signature');
        $event = $request->header('X-Digiflazz-Event');
        $payload = json_decode($rawBody, true);

        $log = ProviderCallbackLog::query()->create([
            'provider' => 'digiflazz',
            'event' => is_string($event) ? $event : null,
            'signature' => is_string($signature) ? $signature : null,
            'headers' => $request->headers->all(),
            'payload' => is_array($payload) ? $payload : null,
            'is_valid_signature' => $this->digiflazzClient->verifyWebhookSignature($rawBody, $signature),
        ]);

        if (! $log->is_valid_signature) {
            $this->alertService->logSecurityWarning('Invalid Digiflazz callback signature received.', [
                'event' => $event,
                'signature' => $signature,
            ]);
            $log->forceFill(['processing_result' => 'invalid_signature'])->save();

            return [
                'status' => 401,
                'body' => ['success' => false, 'message' => 'Invalid signature.'],
            ];
        }

        if (strtolower((string) $event) === 'ping' && is_array($payload)) {
            $log->forceFill([
                'external_id' => isset($payload['hook_id']) ? (string) $payload['hook_id'] : null,
                'processing_result' => 'ping',
                'processed_at' => now(),
            ])->save();

            return [
                'status' => 200,
                'body' => ['success' => true, 'message' => 'Ping received.'],
            ];
        }

        $data = is_array($payload) ? Arr::get($payload, 'data') : null;

        if (! is_array($data) || blank($data['ref_id'] ?? null)) {
            $log->forceFill(['processing_result' => 'invalid_payload'])->save();

            return [
                'status' => 422,
                'body' => ['success' => false, 'message' => 'Invalid callback payload.'],
            ];
        }

        /** @var PpobTransaction|null $transaction */
        $transaction = PpobTransaction::query()
            ->where('digiflazz_ref_id', (string) $data['ref_id'])
            ->first();

        if (! $transaction) {
            $this->alertService->logWarning('Digiflazz callback references unknown transaction.', [
                'ref_id' => (string) $data['ref_id'],
            ]);
            $log->forceFill([
                'external_id' => (string) $data['ref_id'],
                'processing_result' => 'transaction_not_found',
            ])->save();

            return [
                'status' => 404,
                'body' => ['success' => false, 'message' => 'Transaction not found.'],
            ];
        }

        $this->digiflazzPpobService->applyPayload($transaction, $data);

        $log->forceFill([
            'external_id' => $transaction->digiflazz_ref_id,
            'processing_result' => 'processed',
            'processed_at' => now(),
        ])->save();

        return [
            'status' => 200,
            'body' => ['success' => true],
        ];
    }
}
