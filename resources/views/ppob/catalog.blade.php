@php($title = $journeyMeta['title'] . ' - PPOB')

@extends('layouts.web')

@section('content')
    @php($selectionPlaceholder = ($carrierDetection['enabled'] ?? false) ? 'Masukkan nomor HP untuk melihat produk.' : 'Pilih produk untuk lanjut bayar.')

    <div
        class="space-y-5 pb-40 xl:pb-0"
        data-ppob-builder
        data-catalog='@json($catalog)'
        data-carrier-detection-enabled="{{ ($carrierDetection['enabled'] ?? false) ? '1' : '0' }}"
        data-carrier-config='@json($carrierDetection['config'] ?? [])'
    >
        <section class="space-y-4">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-300">
                    @include('ppob.partials.journey-icon', ['journey' => $journey, 'attributes' => new \Illuminate\View\ComponentAttributeBag(['class' => 'h-5 w-5'])])
                </span>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-zinc-950 dark:text-zinc-100 sm:text-3xl">{{ $journeyMeta['title'] }}</h1>
                    @if ($search !== '')
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $search }}</p>
                    @endif
                </div>
            </div>

            @if ($journeyOptions !== [])
                <div class="grid grid-cols-6 gap-2">
                    @foreach ($journeyOptions as $option)
                        @php($isActiveJourney = $option['key'] === $journey)
                        <a
                            href="{{ route('ppob.catalog', ['serviceType' => $serviceType, 'journey' => $option['key']]) }}"
                            class="group flex min-w-0 flex-col items-center justify-center gap-2 rounded-xl border px-2 py-3 text-center text-[11px] font-semibold leading-tight transition-colors sm:text-xs {{ $isActiveJourney ? 'border-emerald-600 bg-emerald-600 text-white dark:border-emerald-500 dark:bg-emerald-500 dark:text-white' : 'border-zinc-200 bg-white text-zinc-700 hover:border-emerald-600 hover:bg-emerald-600 hover:text-white dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:border-emerald-500 dark:hover:bg-emerald-500 dark:hover:text-white' }}"
                        >
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl transition-colors {{ $isActiveJourney ? 'bg-white/15 text-white dark:bg-white/15 dark:text-white' : 'bg-zinc-100 text-zinc-600 group-hover:bg-white/15 group-hover:text-white dark:bg-zinc-800 dark:text-zinc-300 dark:group-hover:bg-white/15 dark:group-hover:text-white' }}">
                                @include('ppob.partials.journey-icon', ['journey' => $option['key'], 'attributes' => new \Illuminate\View\ComponentAttributeBag(['class' => 'h-4 w-4'])])
                            </span>
                            <span class="line-clamp-2">{{ $option['title'] }}</span>
                        </a>
                    @endforeach
                </div>
            @endif

            <div class="rounded-2xl border border-zinc-200 bg-white p-3 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <label for="customer_no_preview" class="sr-only">
                    {{ $journeyMeta['input_label'] ?? ($serviceType === 'prepaid' ? 'Nomor HP / ID' : 'Nomor pelanggan') }}
                </label>
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/30 dark:text-emerald-300">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h10" />
                        </svg>
                    </span>
                    <input
                        id="customer_no_preview"
                        type="text"
                        value="{{ old('customer_no') }}"
                        placeholder="{{ $journeyMeta['input_placeholder'] ?? ($serviceType === 'prepaid' ? 'Masukkan nomor' : 'Masukkan ID pelanggan') }}"
                        class="w-full border-0 bg-transparent px-0 py-2 text-sm text-zinc-900 outline-none ring-0 placeholder:text-zinc-400 focus:outline-none focus:ring-0 dark:text-zinc-100"
                        data-customer-proxy
                    >
                </div>
            </div>
        </section>

        @if ($paymentChannelsError)
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-200">
                {{ $paymentChannelsError }}
            </div>
        @endif

        <section class="xl:grid xl:grid-cols-[minmax(0,1fr)_20rem] xl:gap-6">
            <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-5">
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3" data-product-options></div>
            </div>

            <div class="fixed inset-x-0 bottom-0 z-30 border-t border-zinc-200 bg-white/95 p-4 shadow-[0_-16px_40px_-24px_rgba(15,23,42,0.45)] backdrop-blur xl:static xl:mt-0 xl:border-0 xl:bg-transparent xl:p-0 xl:shadow-none xl:backdrop-blur-none">
                @if ($serviceType === 'prepaid')
                    <form action="{{ route('ppob.catalog.transactions.store', ['serviceType' => $serviceType, 'journey' => $journey]) }}" method="POST" class="mx-auto flex max-w-5xl flex-col gap-3 rounded-2xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900 xl:sticky xl:top-24 xl:max-w-none xl:border xl:p-5">
                        @csrf
                        <input type="hidden" name="service_type" value="prepaid">
                        <input type="hidden" name="buyer_sku_code" value="{{ old('buyer_sku_code') }}" data-product-input>
                        <input id="customer_no" name="customer_no" type="hidden" value="{{ old('customer_no') }}" data-customer-input>

                        <div class="rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm text-zinc-600 dark:border-zinc-800 dark:bg-zinc-900/60 dark:text-zinc-300" data-selection-summary>
                            {{ $selectionPlaceholder }}
                        </div>

                        @if ($paymentChannels !== [])
                            <div>
                                <label for="payment_channel_code" class="sr-only">Metode bayar</label>
                                <select
                                    id="payment_channel_code"
                                    name="payment_channel_code"
                                    class="w-full rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm font-medium text-zinc-900 outline-none transition focus:border-emerald-600 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:focus:border-emerald-500"
                                >
                                    @foreach ($paymentChannels as $channel)
                                        <option value="{{ $channel['code'] }}" @selected(old('payment_channel_code', $paymentChannels[0]['code'] ?? '') === $channel['code'])>
                                            {{ $channel['name'] ?? $channel['code'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <button
                            type="submit"
                            @disabled($paymentChannels === [])
                            class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-emerald-500 dark:bg-emerald-500 dark:hover:bg-emerald-400 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Beli sekarang
                        </button>
                    </form>
                @elseif (is_array($latestInquiry))
                    <form action="{{ route('ppob.catalog.transactions.store', ['serviceType' => $serviceType, 'journey' => $journey]) }}" method="POST" class="mx-auto flex max-w-5xl flex-col gap-3 rounded-2xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900 xl:sticky xl:top-24 xl:max-w-none xl:border xl:p-5">
                        @csrf
                        <input type="hidden" name="service_type" value="postpaid">
                        <input type="hidden" name="inquiry_reference" value="{{ $latestInquiry['reference'] }}">

                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 dark:border-emerald-900/60 dark:bg-emerald-950/20">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700 dark:text-emerald-200">Tagihan ditemukan</p>
                            <p class="mt-2 text-sm font-semibold text-zinc-950 dark:text-zinc-100">{{ $latestInquiry['product_name'] ?? 'Tagihan' }}</p>
                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $latestInquiry['customer_name'] ?? 'Pelanggan' }}</p>
                            <p class="mt-3 text-xl font-semibold text-emerald-700 dark:text-emerald-300">Rp{{ number_format((int) round($latestInquiry['base_price'] ?? 0)) }}</p>
                        </div>

                        @if ($paymentChannels !== [])
                            <div>
                                <label for="postpaid_payment_channel_code" class="sr-only">Metode bayar</label>
                                <select
                                    id="postpaid_payment_channel_code"
                                    name="payment_channel_code"
                                    class="w-full rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm font-medium text-zinc-900 outline-none transition focus:border-emerald-600 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:focus:border-emerald-500"
                                >
                                    @foreach ($paymentChannels as $channel)
                                        <option value="{{ $channel['code'] }}" @selected(old('payment_channel_code', $paymentChannels[0]['code'] ?? '') === $channel['code'])>
                                            {{ $channel['name'] ?? $channel['code'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <button
                            type="submit"
                            @disabled($paymentChannels === [])
                            class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-emerald-500 dark:bg-emerald-500 dark:hover:bg-emerald-400 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Lanjut bayar
                        </button>
                    </form>
                @else
                    <form action="{{ route('ppob.catalog.inquiries.store', ['serviceType' => $serviceType, 'journey' => $journey]) }}" method="POST" class="mx-auto flex max-w-5xl flex-col gap-3 rounded-2xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900 xl:sticky xl:top-24 xl:max-w-none xl:border xl:p-5">
                        @csrf
                        <input type="hidden" name="buyer_sku_code" value="{{ old('buyer_sku_code') }}" data-product-input>
                        <input id="customer_no" name="customer_no" type="hidden" value="{{ old('customer_no') }}" data-customer-input>

                        <div class="rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm text-zinc-600 dark:border-zinc-800 dark:bg-zinc-900/60 dark:text-zinc-300" data-selection-summary>
                            {{ $selectionPlaceholder }}
                        </div>

                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-emerald-500 dark:bg-emerald-500 dark:hover:bg-emerald-400">
                            Cek tagihan
                        </button>
                    </form>
                @endif
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-ppob-builder]').forEach((section) => {
            const catalog = JSON.parse(section.dataset.catalog || '[]');
            const productOptions = section.querySelector('[data-product-options]');
            const hiddenProductInput = section.querySelector('[data-product-input]');
            const summary = section.querySelector('[data-selection-summary]');
            const customerInput = section.querySelector('[data-customer-input]');
            const customerProxyInput = section.querySelector('[data-customer-proxy]');
            const carrierDetectionEnabled = section.dataset.carrierDetectionEnabled === '1';
            const carrierConfig = JSON.parse(section.dataset.carrierConfig || '{}');

            let selectedSku = hiddenProductInput?.value || null;

            const money = new Intl.NumberFormat('id-ID');

            function normalizePhoneNumber(value) {
                const digits = String(value || '').replace(/\D+/g, '');

                if (digits.startsWith('62')) {
                    return '0' + digits.slice(2);
                }

                if (digits.startsWith('8')) {
                    return '0' + digits;
                }

                return digits;
            }

            function isValidMobileNumber(value) {
                return /^08[1-9][0-9]{6,11}$/.test(normalizePhoneNumber(value));
            }

            function detectCarrierBrandKey() {
                if (!carrierDetectionEnabled || !customerInput) {
                    return null;
                }

                const normalized = normalizePhoneNumber(customerInput.value);

                if (!isValidMobileNumber(normalized)) {
                    return null;
                }

                const prefixes = carrierConfig.prefixes || {};

                for (const [brandKey, prefixList] of Object.entries(prefixes)) {
                    if (!Array.isArray(prefixList)) {
                        continue;
                    }

                    if (prefixList.some((prefix) => normalized.startsWith(prefix))) {
                        return brandKey;
                    }
                }

                return null;
            }

            function escapeRegex(value) {
                return String(value || '').replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            }

            function normalizeCompareText(value) {
                return String(value || '')
                    .toLowerCase()
                    .replace(/[^a-z0-9]+/g, ' ')
                    .trim();
            }

            function containsBrandInText(text, brand) {
                const normalizedText = normalizeCompareText(text);
                const normalizedBrand = normalizeCompareText(brand);

                if (normalizedText === '' || normalizedBrand === '') {
                    return false;
                }

                return normalizedText.includes(normalizedBrand);
            }

            function finalizePresentation(presentation, brand) {
                const title = String(presentation.title || '').trim();
                const subtitle = String(presentation.subtitle || '').trim();

                if (subtitle === '') {
                    return {
                        title,
                        subtitle: '',
                        showBrandLabel: !containsBrandInText(title, brand),
                    };
                }

                const shouldHideSubtitle = containsBrandInText(title, subtitle) || normalizeCompareText(subtitle) === normalizeCompareText(brand);

                return {
                    title,
                    subtitle: shouldHideSubtitle ? '' : subtitle,
                    showBrandLabel: !containsBrandInText(title, brand),
                };
            }

            function buildProductPresentation(product) {
                const rawName = String(product.name || '').trim();
                const brand = String(product.brand || '').trim();
                const trimmedName = brand !== ''
                    ? rawName.replace(new RegExp(`^${escapeRegex(brand)}\\s*`, 'i'), '').trim()
                    : rawName;

                if (product.journey === 'data') {
                    const quota = rawName.match(/(\d+(?:[.,]\d+)?)\s*(GB|MB|TB)/i);
                    const period = rawName.match(/(\d+)\s*(Hari|Hr|Jam)/i);

                    if (quota) {
                        return finalizePresentation({
                            title: `${quota[1]} ${quota[2].toUpperCase()}`,
                            subtitle: period ? `${period[1]} ${period[2]}` : (trimmedName || brand),
                        }, brand);
                    }
                }

                if (product.journey === 'pulsa' || product.journey === 'pln_token') {
                    const nominal = rawName.match(/(\d{1,3}(?:[.,]\d{3})+)/);

                    if (nominal) {
                        return finalizePresentation({
                            title: nominal[1],
                            subtitle: product.journey === 'pln_token' ? 'Token listrik' : brand,
                        }, brand);
                    }
                }

                if (product.journey === 'voucher') {
                    const voucherAmount = rawName.match(/(\d+(?:[.,]\d+)?)\s*(Diamond|Diamonds|DM|UC|Point|Points)/i);

                    if (voucherAmount) {
                        return finalizePresentation({
                            title: `${voucherAmount[1]} ${voucherAmount[2]}`,
                            subtitle: brand,
                        }, brand);
                    }
                }

                return finalizePresentation({
                    title: trimmedName !== '' ? trimmedName : rawName,
                    subtitle: brand,
                }, brand);
            }

            function renderProducts() {
                const carrierBrandKey = detectCarrierBrandKey();
                let products = catalog;

                if (carrierDetectionEnabled && !carrierBrandKey) {
                    selectedSku = null;

                    if (hiddenProductInput) {
                        hiddenProductInput.value = '';
                    }

                    productOptions.innerHTML = '<div class="rounded-xl border border-zinc-200 bg-white px-5 py-14 text-center dark:border-zinc-800 dark:bg-zinc-950 sm:col-span-2 xl:col-span-3"><p class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Masukkan nomor HP yang valid</p><p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Produk akan tampil otomatis setelah nomor dikenali.</p></div>';
                    return;
                }

                if (carrierBrandKey) {
                    products = catalog.filter((item) => item.brand_key === carrierBrandKey);
                }

                if (!products.length) {
                    productOptions.innerHTML = '<p class="px-2 py-8 text-sm text-zinc-500 dark:text-zinc-400">Produk tidak tersedia.</p>';
                    return;
                }

                if (!products.some((item) => item.sku === selectedSku)) {
                    selectedSku = products[0].sku;
                }

                if (hiddenProductInput) {
                    hiddenProductInput.value = selectedSku ?? '';
                }

                productOptions.innerHTML = products.map((product) => {
                    const active = product.sku === selectedSku;
                    const presentation = buildProductPresentation(product);

                    return `<button type="button" data-product-option="${product.sku}" class="rounded-xl border px-4 py-4 text-left transition-colors ${active ? 'border-emerald-600 bg-emerald-600 text-white shadow-sm dark:border-emerald-500 dark:bg-emerald-500 dark:text-white' : 'border-zinc-200 bg-white hover:border-emerald-600 hover:bg-emerald-50 dark:border-zinc-800 dark:bg-zinc-950 dark:hover:border-emerald-500 dark:hover:bg-emerald-500/10'}">
                        ${presentation.showBrandLabel ? `<span class="block text-[11px] font-semibold uppercase tracking-[0.18em] ${active ? 'text-emerald-100 dark:text-emerald-50' : 'text-zinc-400 dark:text-zinc-500'}">${product.brand}</span>` : ''}
                        <span class="block text-lg font-bold leading-tight ${active ? 'text-white' : 'text-zinc-950 dark:text-zinc-100'}">${presentation.title}</span>
                        <span class="mt-1 block text-sm ${active ? 'text-emerald-100 dark:text-emerald-50' : 'text-zinc-500 dark:text-zinc-400'}">${presentation.subtitle || '&nbsp;'}</span>
                        <span class="mt-5 block text-lg font-semibold ${active ? 'text-white' : 'text-emerald-700 dark:text-emerald-300'}">Rp${money.format(product.price)}</span>
                    </button>`;
                }).join('');

                productOptions.querySelectorAll('[data-product-option]').forEach((button) => {
                    button.addEventListener('click', () => {
                        selectedSku = button.dataset.productOption;

                        if (hiddenProductInput) {
                            hiddenProductInput.value = selectedSku;
                        }

                        renderProducts();
                        updateSummary();
                    });
                });
            }

            function updateSummary() {
                const selectedProduct = catalog.find((item) => item.sku === selectedSku);
                const destination = String(customerInput?.value || '').trim();
                const presentation = selectedProduct ? buildProductPresentation(selectedProduct) : null;

                if (!selectedProduct || !presentation) {
                    summary.textContent = carrierDetectionEnabled ? 'Masukkan nomor HP untuk melihat produk.' : 'Pilih produk untuk lanjut bayar.';
                    return;
                }

                summary.innerHTML = `
                    ${presentation.showBrandLabel ? `<span class="block text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">${selectedProduct.brand}</span>` : ''}
                    <span class="mt-2 block text-base font-semibold text-zinc-950 dark:text-zinc-100">${presentation.title}</span>
                    <span class="mt-1 block text-sm text-zinc-500 dark:text-zinc-400">${presentation.subtitle || ''}${destination ? ` • ${destination}` : ''}</span>
                    <span class="mt-3 block text-lg font-semibold text-emerald-700 dark:text-emerald-300">Rp${money.format(selectedProduct.price)}</span>
                `;
            }

            if (customerProxyInput && customerInput) {
                const syncCustomerInput = () => {
                    customerInput.value = customerProxyInput.value;
                };

                syncCustomerInput();
                customerProxyInput.addEventListener('input', () => {
                    syncCustomerInput();
                    selectedSku = null;

                    if (hiddenProductInput) {
                        hiddenProductInput.value = '';
                    }

                    renderProducts();
                    updateSummary();
                });
            }

            renderProducts();
            updateSummary();
        });
    </script>
@endpush
