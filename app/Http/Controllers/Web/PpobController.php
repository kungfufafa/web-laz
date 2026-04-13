<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePpobInquiryRequest;
use App\Http\Requests\Api\StorePpobTransactionRequest;
use App\Models\PpobProduct;
use App\Models\PpobTransaction;
use App\Models\User;
use App\Services\MidtransClient;
use App\Services\PpobTransactionService;
use App\Support\PhoneCarrier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PpobController extends Controller
{
    public function index(): View
    {
        $prepaidProducts = $this->availableProducts(PpobTransaction::SERVICE_PREPAID, '');
        $postpaidProducts = $this->availableProducts(PpobTransaction::SERVICE_POSTPAID, '');

        return view('welcome', [
            'prepaidJourneyOptions' => $this->journeyOptions($prepaidProducts, PpobTransaction::SERVICE_PREPAID),
            'postpaidJourneyOptions' => $this->journeyOptions($postpaidProducts, PpobTransaction::SERVICE_POSTPAID),
            'activeGateway' => strtolower((string) config('services.ppob.payment_gateway', 'midtrans')),
        ]);
    }

    public function catalog(Request $request, string $serviceType, string $journey, PpobTransactionService $transactionService): View
    {
        $serviceType = $this->normalizeServiceType($serviceType);
        $journey = strtolower(trim($journey));
        $search = trim((string) $request->query('search', ''));
        $availableProducts = $this->availableProducts($serviceType, '');
        $journeyOptions = $this->journeyOptions($availableProducts, $serviceType);

        abort_unless(collect($journeyOptions)->contains('key', $journey), 404);

        $filteredProducts = $search === ''
            ? $availableProducts
            : $this->availableProducts($serviceType, $search);
        $journeyProducts = $this->productsForJourney($filteredProducts, $serviceType, $journey);

        $paymentChannelsError = null;

        try {
            $paymentChannels = $transactionService->paymentChannels();
        } catch (\Throwable $exception) {
            report($exception);

            $paymentChannels = [];
            $paymentChannelsError = 'Kanal pembayaran belum dapat dimuat. Silakan coba lagi beberapa saat.';
        }

        $journeyMeta = collect($journeyOptions)->firstWhere('key', $journey) ?? $this->journeyDefinition($journey, $serviceType);
        $latestInquiry = $this->scopedInquiry(
            session('ppob_inquiry'),
            $serviceType,
            $journey,
        );

        return view('ppob.catalog', [
            'serviceType' => $serviceType,
            'journey' => $journey,
            'journeyMeta' => $journeyMeta,
            'journeyOptions' => $journeyOptions,
            'catalog' => $this->catalogPayload($journeyProducts, $serviceType),
            'carrierDetection' => [
                'enabled' => $serviceType === PpobTransaction::SERVICE_PREPAID && in_array($journey, ['pulsa', 'data'], true),
                'config' => PhoneCarrier::browserConfig(),
            ],
            'paymentChannels' => $paymentChannels,
            'paymentChannelsError' => $paymentChannelsError,
            'latestInquiry' => $latestInquiry,
            'search' => $search,
        ]);
    }

    public function catalogInquiry(StorePpobInquiryRequest $request, string $serviceType, string $journey, PpobTransactionService $transactionService): RedirectResponse
    {
        $serviceType = $this->normalizeServiceType($serviceType);
        $journey = strtolower(trim($journey));
        $user = $this->resolveCheckoutUser($request);

        try {
            $inquiry = $transactionService->createInquiry($request->validated(), $user);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors([
                    'inquiry' => 'Inquiry PPOB belum dapat diproses sekarang. Silakan coba lagi.',
                ]);
        }

        $request->session()->put('ppob_inquiry', [
            ...$inquiry,
            'service_type' => $serviceType,
            'journey' => $journey,
        ]);

        return redirect()
            ->route('ppob.catalog', ['serviceType' => $serviceType, 'journey' => $journey])
            ->with('status', 'Inquiry berhasil dibuat. Lanjutkan ke pembayaran untuk menyelesaikan transaksi.');
    }

    public function catalogStore(StorePpobTransactionRequest $request, string $serviceType, string $journey, PpobTransactionService $transactionService): RedirectResponse
    {
        $this->normalizeServiceType($serviceType);
        $journey = strtolower(trim($journey));
        $user = $this->resolveCheckoutUser($request);

        try {
            $transaction = $transactionService->createTransaction($request->validated(), $user);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()
                ->route('ppob.catalog', ['serviceType' => $serviceType, 'journey' => $journey])
                ->withInput()
                ->withErrors([
                    'transaction' => 'Transaksi PPOB belum dapat dibuat sekarang. Silakan coba lagi.',
                ]);
        }

        $this->rememberGuestTransaction($request, $transaction);
        $request->session()->forget('ppob_inquiry');

        return redirect()
            ->route('ppob.transactions.show', $transaction)
            ->with('status', 'Transaksi PPOB berhasil dibuat.');
    }

    public function show(PpobTransaction $ppobTransaction, MidtransClient $midtransClient): View
    {
        abort_unless($this->canAccessTransaction(request(), $ppobTransaction), 404);

        $transaction = $ppobTransaction->fresh() ?? $ppobTransaction;
        $paymentInstructions = data_get($transaction->metadata, 'payment_instructions', []);
        $checkoutUrl = $transaction->midtrans_redirect_url ?? $transaction->payment_gateway_checkout_url;
        $payUrl = $transaction->payment_gateway_pay_url;
        $payCode = data_get($transaction->metadata, 'payment_code')
            ?? $transaction->payment_gateway_pay_code
            ?? data_get($transaction->midtrans_payload, 'va_numbers.0.va_number')
            ?? data_get($transaction->midtrans_payload, 'permata_va_number')
            ?? data_get($transaction->midtrans_payload, 'payment_code')
            ?? data_get($transaction->midtrans_payload, 'bill_key');
        $paymentReference = $transaction->midtrans_transaction_id ?? $transaction->payment_gateway_reference;
        $paymentOrderId = $transaction->midtrans_order_id ?? $transaction->payment_gateway_order_id;
        $expiresAt = $transaction->midtrans_expired_at ?? $transaction->payment_gateway_expired_at;

        return view('ppob.show', [
            'transaction' => $transaction,
            'checkoutUrl' => $checkoutUrl,
            'payUrl' => $payUrl,
            'payCode' => $payCode,
            'paymentReference' => $paymentReference,
            'paymentOrderId' => $paymentOrderId,
            'paymentInstructions' => is_array($paymentInstructions) ? $paymentInstructions : [],
            'expiresAt' => $expiresAt,
            'midtransClientKey' => $midtransClient->resolvedClientKey(),
            'midtransSnapScriptUrl' => (bool) config('services.midtrans.is_production')
                ? 'https://app.midtrans.com/snap/snap.js'
                : 'https://app.sandbox.midtrans.com/snap/snap.js',
        ]);
    }

    public function refresh(PpobTransaction $ppobTransaction, PpobTransactionService $transactionService): RedirectResponse
    {
        abort_unless($this->canAccessTransaction(request(), $ppobTransaction), 404);

        try {
            $transaction = $transactionService->refreshTransactionStatus($ppobTransaction->uuid);
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors([
                'refresh' => 'Status transaksi belum bisa diperbarui sekarang. Silakan coba lagi.',
            ]);
        }

        return redirect()
            ->route('ppob.transactions.show', $transaction)
            ->with('status', 'Status transaksi berhasil diperbarui.');
    }

    private function availableProducts(string $serviceType, string $search): \Illuminate\Support\Collection
    {
        return PpobProduct::query()
            ->where('service_type', $serviceType)
            ->where('buyer_product_status', true)
            ->where('seller_product_status', true)
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner
                        ->where('product_name', 'like', '%'.$search.'%')
                        ->orWhere('brand', 'like', '%'.$search.'%')
                        ->orWhere('buyer_sku_code', 'like', '%'.$search.'%')
                        ->orWhere('category', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('category')
            ->orderBy('brand')
            ->orderBy('product_name')
            ->get();
    }

    private function productsForJourney(Collection $products, string $serviceType, string $journey): Collection
    {
        return $products
            ->filter(fn (PpobProduct $product): bool => $this->resolveJourneyKey($product, $serviceType) === $journey)
            ->values();
    }

    private function catalogPayload(Collection $products, string $serviceType): array
    {
        return $products
            ->map(function (PpobProduct $product) use ($serviceType): array {
                $journeyKey = $this->resolveJourneyKey($product, $serviceType);
                $journey = $this->journeyDefinition($journeyKey, $serviceType);

                return [
                    'journey' => $journeyKey,
                    'journey_title' => $journey['title'],
                    'brand' => $this->resolveBrandLabel($product),
                    'brand_key' => PhoneCarrier::matchBrandKey($this->resolveBrandLabel($product)),
                    'sku' => $product->buyer_sku_code,
                    'name' => $product->product_name,
                    'price' => (int) round($product->resolvedSellPrice() ?? 0),
                    'provider_price' => (int) round($product->resolvedProviderPrice() ?? 0),
                    'category' => (string) ($product->category ?? ''),
                    'type' => (string) ($product->type ?? ''),
                    'description' => (string) ($product->description ?? ''),
                    'service_type' => $serviceType,
                ];
            })
            ->values()
            ->all();
    }

    private function journeyOptions(Collection $products, string $serviceType): array
    {
        return $products
            ->groupBy(fn (PpobProduct $product): string => $this->resolveJourneyKey($product, $serviceType))
            ->map(function (Collection $group, string $journeyKey) use ($serviceType): array {
                $journey = $this->journeyDefinition($journeyKey, $serviceType);

                return [
                    ...$journey,
                    'key' => $journeyKey,
                    'count' => $group->count(),
                ];
            })
            ->sortBy('sort')
            ->values()
            ->all();
    }

    private function resolveJourneyKey(PpobProduct $product, string $serviceType): string
    {
        $haystack = Str::lower(implode(' ', array_filter([
            $product->category,
            $product->brand,
            $product->type,
            $product->product_name,
        ])));

        if ($serviceType === PpobTransaction::SERVICE_PREPAID) {
            return match (true) {
                $this->containsAny($haystack, ['pulsa']) => 'pulsa',
                $this->containsAny($haystack, ['data', 'internet']) => 'data',
                $this->containsAny($haystack, ['pln', 'token listrik', 'listrik']) => 'pln_token',
                $this->containsAny($haystack, ['ovo', 'gopay', 'dana', 'shopeepay', 'linkaja', 'e-money', 'emoney']) => 'e_wallet',
                $this->containsAny($haystack, ['voucher', 'game', 'garena', 'steam', 'mobile legends', 'free fire']) => 'voucher',
                default => 'lainnya_prepaid',
            };
        }

        return match (true) {
            $this->containsAny($haystack, ['pln', 'listrik']) => 'pln_bill',
            $this->containsAny($haystack, ['bpjs']) => 'bpjs',
            $this->containsAny($haystack, ['pdam']) => 'pdam',
            $this->containsAny($haystack, ['telkom', 'indihome', 'internet', 'tv']) => 'internet_tv',
            $this->containsAny($haystack, ['finance', 'multifinance', 'angsuran', 'leasing', 'kredit']) => 'finance',
            default => 'lainnya_postpaid',
        };
    }

    private function journeyDefinition(string $journeyKey, string $serviceType): array
    {
        $definitions = $serviceType === PpobTransaction::SERVICE_PREPAID
            ? [
                'pulsa' => ['title' => 'Isi Pulsa', 'tone' => 'emerald', 'sort' => 10, 'input_label' => 'Nomor HP', 'input_placeholder' => 'Masukkan nomor HP'],
                'data' => ['title' => 'Paket Data', 'tone' => 'sky', 'sort' => 20, 'input_label' => 'Nomor HP', 'input_placeholder' => 'Masukkan nomor HP'],
                'pln_token' => ['title' => 'Token PLN', 'tone' => 'amber', 'sort' => 30, 'input_label' => 'ID Pelanggan / No. Meter', 'input_placeholder' => 'Masukkan ID pelanggan atau no. meter'],
                'e_wallet' => ['title' => 'Uang Elektronik', 'tone' => 'violet', 'sort' => 40, 'input_label' => 'Nomor akun / tujuan', 'input_placeholder' => 'Masukkan nomor akun atau tujuan'],
                'voucher' => ['title' => 'Voucher', 'tone' => 'rose', 'sort' => 50, 'input_label' => 'User ID / Zone ID', 'input_placeholder' => 'Masukkan user ID atau zone ID'],
                'lainnya_prepaid' => ['title' => 'Lainnya', 'tone' => 'zinc', 'sort' => 90, 'input_label' => 'Nomor / ID tujuan', 'input_placeholder' => 'Masukkan nomor atau ID tujuan'],
            ]
            : [
                'pln_bill' => ['title' => 'Tagihan PLN', 'tone' => 'amber', 'sort' => 10, 'input_label' => 'ID Pelanggan', 'input_placeholder' => 'Masukkan ID pelanggan'],
                'bpjs' => ['title' => 'BPJS', 'tone' => 'emerald', 'sort' => 20, 'input_label' => 'Nomor peserta / VA', 'input_placeholder' => 'Masukkan nomor peserta atau VA'],
                'pdam' => ['title' => 'PDAM', 'tone' => 'sky', 'sort' => 30, 'input_label' => 'Nomor pelanggan', 'input_placeholder' => 'Masukkan nomor pelanggan'],
                'internet_tv' => ['title' => 'Internet & TV', 'tone' => 'violet', 'sort' => 40, 'input_label' => 'ID pelanggan', 'input_placeholder' => 'Masukkan ID pelanggan'],
                'finance' => ['title' => 'Cicilan', 'tone' => 'rose', 'sort' => 50, 'input_label' => 'Nomor kontrak', 'input_placeholder' => 'Masukkan nomor kontrak'],
                'lainnya_postpaid' => ['title' => 'Lainnya', 'tone' => 'zinc', 'sort' => 90, 'input_label' => 'Nomor pelanggan', 'input_placeholder' => 'Masukkan nomor pelanggan'],
            ];

        return $definitions[$journeyKey] ?? ['title' => 'Lainnya', 'tone' => 'zinc', 'sort' => 99, 'input_label' => 'Nomor / ID', 'input_placeholder' => 'Masukkan nomor atau ID'];
    }

    private function resolveBrandLabel(PpobProduct $product): string
    {
        $label = trim((string) ($product->brand ?: $product->category ?: $product->type ?: 'Umum'));

        return $label !== '' ? $label : 'Umum';
    }

    private function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($needle !== '' && str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function scopedInquiry(mixed $inquiry, string $serviceType, string $journey): ?array
    {
        if (! is_array($inquiry)) {
            return null;
        }

        if (($inquiry['service_type'] ?? null) !== $serviceType) {
            return null;
        }

        if (($inquiry['journey'] ?? null) !== $journey) {
            return null;
        }

        return $inquiry;
    }

    private function normalizeServiceType(string $serviceType): string
    {
        $serviceType = strtolower(trim($serviceType));

        abort_unless(in_array($serviceType, [
            PpobTransaction::SERVICE_PREPAID,
            PpobTransaction::SERVICE_POSTPAID,
        ], true), 404);

        return $serviceType;
    }

    private function resolveCheckoutUser(Request $request): User
    {
        $authenticatedUser = $request->user();

        if ($authenticatedUser instanceof User) {
            return $authenticatedUser;
        }

        $guestUserId = $request->session()->get('ppob_guest_user_id');

        if (is_int($guestUserId) || (is_string($guestUserId) && ctype_digit($guestUserId))) {
            $guestUser = User::query()->find((int) $guestUserId);

            if ($guestUser instanceof User) {
                return $guestUser;
            }
        }

        $guestToken = (string) $request->session()->get('ppob_guest_token', Str::uuid()->toString());
        $request->session()->put('ppob_guest_token', $guestToken);

        $guestUser = User::query()->create([
            'name' => 'Guest PPOB',
            'email' => sprintf('ppob-guest-%s@lazalazhar5.local', $guestToken),
            'password' => Hash::make(Str::password(32)),
        ]);

        $request->session()->put('ppob_guest_user_id', $guestUser->id);

        return $guestUser;
    }

    private function rememberGuestTransaction(Request $request, PpobTransaction $transaction): void
    {
        if ($request->user() instanceof User) {
            return;
        }

        $knownTransactions = collect($request->session()->get('ppob_guest_transactions', []))
            ->filter(fn (mixed $uuid): bool => is_string($uuid) && $uuid !== '')
            ->push($transaction->uuid)
            ->unique()
            ->values()
            ->all();

        $request->session()->put('ppob_guest_transactions', $knownTransactions);
    }

    private function canAccessTransaction(Request $request, PpobTransaction $transaction): bool
    {
        $authenticatedUser = $request->user();

        if ($authenticatedUser instanceof User && $transaction->user_id === $authenticatedUser->id) {
            return true;
        }

        if ($authenticatedUser instanceof User) {
            return false;
        }

        $guestUserId = $request->session()->get('ppob_guest_user_id');

        if ((is_int($guestUserId) || (is_string($guestUserId) && ctype_digit($guestUserId))) && $transaction->user_id === (int) $guestUserId) {
            return true;
        }

        return collect($request->session()->get('ppob_guest_transactions', []))
            ->contains($transaction->uuid);
    }
}
