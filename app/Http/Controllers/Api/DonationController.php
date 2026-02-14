<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreDonationRequest;
use App\Http\Resources\DonationResource;
use App\Http\Resources\PaymentMethodResource;
use App\Models\Donation;
use App\Models\PaymentMethod;
use App\Services\QrisPayloadService;
use App\Services\ZakatCalculatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class DonationController extends Controller
{
    public function donationConfig(): JsonResponse
    {
        $categories = [];
        foreach (config('donation.categories') as $key => $category) {
            $categories[] = [
                'key' => $key,
                'label' => $category['label'],
                'payment_types' => collect($category['payment_types'])
                    ->map(fn (string $label, string $typeKey): array => [
                        'key' => $typeKey,
                        'label' => $label,
                    ])
                    ->values()
                    ->all(),
            ];
        }

        $calculatorTypes = collect(config('donation.zakat_calculator_types'))
            ->map(fn (string $type): array => [
                'key' => $type,
                'label' => config("donation.categories.zakat.payment_types.{$type}", ucfirst($type)),
            ])
            ->all();

        return response()->json([
            'categories' => $categories,
            'contexts' => config('donation.contexts'),
            'zakat' => [
                'calculator_types' => $calculatorTypes,
                'defaults' => config('donation.zakat_defaults'),
            ],
            'recommended_amounts' => config('donation.recommended_amounts'),
        ]);
    }

    public function calculateZakat(Request $request, ZakatCalculatorService $calculator): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(config('donation.zakat_calculator_types'))],
            'people_count' => ['nullable', 'integer', 'min:1'],
            'rice_price_per_kg' => ['nullable', 'numeric', 'min:1000'],
            'total_assets' => ['nullable', 'numeric', 'min:0'],
            'short_term_debt' => ['nullable', 'numeric', 'min:0'],
            'gold_price_per_gram' => ['nullable', 'numeric', 'min:1000'],
            'gold_grams_nisab' => ['nullable', 'numeric', 'min:1'],
            'haul_passed' => ['nullable', 'boolean'],
            'monthly_income' => ['nullable', 'numeric', 'min:0'],
            'monthly_needs' => ['nullable', 'numeric', 'min:0'],
            'period_months' => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);

        $result = $calculator->calculate($validated['type'], $validated);

        if ($result === null) {
            return response()->json([
                'message' => 'Unsupported zakat calculator type.',
            ], 422);
        }

        return response()->json([
            'type' => $validated['type'],
            ...$result,
        ]);
    }

    public function paymentMethods()
    {
        $methods = PaymentMethod::where('is_active', true)->get();

        return PaymentMethodResource::collection($methods);
    }

    public function store(StoreDonationRequest $request, QrisPayloadService $qrisPayloadService): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user('sanctum');
        $category = $request->normalizedCategory();

        $calculatorType = $validated['calculator_type']
            ?? ($category === 'zakat' ? $validated['payment_type'] : null);

        $paymentMethod = PaymentMethod::query()->findOrFail((int) $validated['payment_method_id']);
        $dynamicQrisPayload = null;

        if ($this->isQrisMethod($paymentMethod)) {
            if (blank($paymentMethod->qris_static_payload)) {
                return response()->json([
                    'message' => 'QRIS template is not configured for this payment method.',
                    'errors' => [
                        'payment_method_id' => [
                            'Selected QRIS payment method does not have a static QR template.',
                        ],
                    ],
                ], 422);
            }

            try {
                $dynamicQrisPayload = $qrisPayloadService->generateDynamicPayload(
                    $paymentMethod->qris_static_payload,
                    (float) $validated['amount'],
                );
            } catch (InvalidArgumentException $exception) {
                return response()->json([
                    'message' => 'Invalid QRIS template payload configuration.',
                    'errors' => [
                        'payment_method_id' => [$exception->getMessage()],
                    ],
                ], 422);
            }
        }

        $path = $request->hasFile('proof_image')
            ? $request->file('proof_image')->store('proofs', 'public')
            : null;

        $donation = Donation::create([
            'user_id' => $user?->id,
            'guest_token' => $validated['guest_token'] ?? null,
            'donor_name' => $validated['donor_name'] ?? $user?->name,
            'donor_phone' => $validated['donor_phone'] ?? null,
            'donor_email' => $validated['donor_email'] ?? $user?->email,
            'payment_method_id' => $validated['payment_method_id'],
            'amount' => $validated['amount'],
            'category' => $category,
            'payment_type' => $validated['payment_type'],
            'context_slug' => $validated['context_slug'] ?? null,
            'context_label' => $validated['context_label'] ?? null,
            'intention_note' => $validated['intention_note'] ?? null,
            'calculator_type' => $calculatorType,
            'calculator_breakdown' => $validated['calculator_breakdown'] ?? null,
            'proof_image' => $path,
            'status' => 'pending',
        ]);

        $donation->load('paymentMethod');

        if ($dynamicQrisPayload !== null) {
            $donation->setAttribute('qris_dynamic_payload', $dynamicQrisPayload);
        }

        return (new DonationResource($donation))
            ->response()
            ->setStatusCode(201);
    }

    public function history(Request $request)
    {
        $user = $request->user('sanctum');
        $query = Donation::query()->with('paymentMethod')->latest();

        if ($user) {
            $query->where('user_id', $user->id);
        } else {
            $guestToken = $request->query('guest_token');
            if (! is_string($guestToken) || trim($guestToken) === '') {
                return response()->json([
                    'message' => 'guest_token is required for guest donation history.',
                    'errors' => [
                        'guest_token' => ['The guest_token query parameter is required.'],
                    ],
                ], 422);
            }

            $query
                ->whereNull('user_id')
                ->where('guest_token', $guestToken);
        }

        $donations = $query->paginate(10);

        return DonationResource::collection($donations);
    }

    private function isQrisMethod(PaymentMethod $paymentMethod): bool
    {
        return str_contains(strtolower($paymentMethod->type), 'qris');
    }
}
