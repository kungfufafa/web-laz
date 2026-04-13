<?php

namespace App\Services;

use App\Models\PpobTransaction;
use Illuminate\Support\Facades\Log;

class PpobAlertService
{
    public function logWarning(string $message, array $context = []): void
    {
        Log::channel('ppob')->warning($message, $context);
    }

    public function logError(string $message, array $context = []): void
    {
        Log::channel('ppob')->error($message, $context);
    }

    public function logSecurityWarning(string $message, array $context = []): void
    {
        Log::channel('ppob-alerts')->warning($message, $context);
    }

    public function monitorRecentFailures(int $minutes, int $threshold): array
    {
        $windowMinutes = max(1, $minutes);
        $failureThreshold = max(1, $threshold);
        $since = now()->subMinutes($windowMinutes);

        $failedTransactions = PpobTransaction::query()
            ->where('updated_at', '>=', $since)
            ->where(function ($query): void {
                $query
                    ->whereIn('payment_status', [
                        PpobTransaction::PAYMENT_FAILED,
                        PpobTransaction::PAYMENT_EXPIRED,
                        PpobTransaction::PAYMENT_REVERSED,
                    ])
                    ->orWhereIn('fulfillment_status', [
                        PpobTransaction::FULFILLMENT_FAILED,
                        PpobTransaction::FULFILLMENT_MANUAL_REVIEW,
                    ]);
            })
            ->pluck('uuid')
            ->all();

        $stuckTransactions = PpobTransaction::query()
            ->where('updated_at', '>=', $since)
            ->where('payment_status', PpobTransaction::PAYMENT_PAID)
            ->whereIn('fulfillment_status', [
                PpobTransaction::FULFILLMENT_PENDING,
                PpobTransaction::FULFILLMENT_PROCESSING,
            ])
            ->pluck('uuid')
            ->all();

        $summary = [
            'window_minutes' => $windowMinutes,
            'threshold' => $failureThreshold,
            'failed_count' => count($failedTransactions),
            'stuck_count' => count($stuckTransactions),
            'failed_transactions' => array_slice($failedTransactions, 0, 10),
            'stuck_transactions' => array_slice($stuckTransactions, 0, 10),
        ];

        $triggered = $summary['failed_count'] >= $failureThreshold
            || $summary['stuck_count'] >= $failureThreshold;

        if ($triggered) {
            Log::channel('ppob-alerts')->critical('PPOB failure threshold reached.', $summary);
        } else {
            Log::channel('ppob')->info('PPOB failure monitor executed.', $summary);
        }

        $summary['triggered'] = $triggered;

        return $summary;
    }
}
