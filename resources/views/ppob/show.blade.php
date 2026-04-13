@php($title = 'Detail Transaksi PPOB')
@php($resolvedGateway = $transaction->resolvedPaymentGateway())
@php($checkoutTarget = $payUrl ?? $checkoutUrl)
@php($hasInlineMidtransPayment = $resolvedGateway === 'midtrans' && $transaction->payment_status === 'unpaid' && $transaction->midtrans_snap_token && $midtransClientKey)
@php($showPaymentReference = filled($paymentReference) && $paymentReference !== $paymentOrderId)
@php($showInstructions = $transaction->payment_status === 'unpaid' && ($payCode || $paymentInstructions !== []))
@php($state = match (true) {
    $transaction->payment_status === 'reversed' => [
        'eyebrow' => 'Pembayaran direversal',
        'title' => 'Pembayaran sudah dibatalkan balik.',
        'description' => 'Pembayaran sempat tercatat berhasil, lalu direfund atau dibatalkan oleh gateway. Transaksi ini perlu ditinjau tim.',
    ],
    $transaction->payment_status === 'paid' && $transaction->fulfillment_status === 'manual_review' => [
        'eyebrow' => 'Perlu tindak lanjut',
        'title' => 'Transaksi perlu ditinjau manual.',
        'description' => 'Pembayaran sudah diterima, tetapi transaksi PPOB tidak bisa dilanjutkan otomatis dan sedang menunggu penanganan tim.',
    ],
    $transaction->payment_status === 'paid' && $transaction->fulfillment_status === 'succeeded' => [
        'eyebrow' => 'Pembayaran berhasil',
        'title' => 'Transaksi sudah dibayar.',
        'description' => 'Pembayaran Anda sudah diterima dan transaksi sedang atau sudah diteruskan ke proses PPOB.',
    ],
    $transaction->payment_status === 'paid' => [
        'eyebrow' => 'Pembayaran diterima',
        'title' => 'Pembayaran sudah masuk.',
        'description' => 'Transaksi sedang menunggu pembaruan proses PPOB. Anda bisa refresh status beberapa saat lagi.',
    ],
    $transaction->payment_status === 'expired' => [
        'eyebrow' => 'Transaksi kedaluwarsa',
        'title' => 'Batas waktu pembayaran sudah lewat.',
        'description' => 'Buat transaksi baru jika Anda masih ingin melanjutkan pembelian produk ini.',
    ],
    $transaction->payment_status === 'failed' => [
        'eyebrow' => 'Pembayaran belum berhasil',
        'title' => 'Transaksi ini belum selesai.',
        'description' => 'Silakan cek kembali metode bayar yang dipakai atau buat transaksi baru.',
    ],
    default => [
        'eyebrow' => 'Menunggu pembayaran',
        'title' => 'Selesaikan pembayaran untuk lanjut.',
        'description' => 'Gunakan tombol bayar di halaman ini atau ikuti instruksi yang tersedia di bawah.',
    ],
})

@extends('layouts.web')

@section('content')
    <div class="space-y-6">
        <section class="rounded-2xl border border-zinc-200 bg-white p-8 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                <div class="max-w-2xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-emerald-600 dark:text-emerald-400">{{ $state['eyebrow'] }}</p>
                    <h1 class="mt-3 text-3xl font-bold tracking-tight text-zinc-950 dark:text-zinc-100 sm:text-4xl">{{ $state['title'] }}</h1>
                    <p class="mt-3 text-sm leading-7 text-zinc-600 dark:text-zinc-400">{{ $state['description'] }}</p>

                    <div class="mt-5 rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-4 dark:border-zinc-800 dark:bg-zinc-900/60">
                        <p class="text-sm font-semibold text-zinc-950 dark:text-zinc-100">{{ $transaction->product_name }}</p>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $transaction->customer_no }}
                            @if ($transaction->payment_channel_name)
                                • {{ $transaction->payment_channel_name }}
                            @endif
                        </p>
                    </div>
                </div>

                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-6 py-5 dark:border-emerald-900/60 dark:bg-emerald-950/20 lg:min-w-64">
                    <p class="text-sm text-emerald-700 dark:text-emerald-200">Total transaksi</p>
                    <p class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-zinc-100">Rp{{ number_format((int) round($transaction->total_amount)) }}</p>
                </div>
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
            <div class="space-y-6">
                @if ($showInstructions)
                    <div class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
                        <h2 class="text-xl font-semibold text-zinc-950 dark:text-zinc-100">Cara bayar</h2>

                        @if ($payCode)
                            <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-4 dark:border-emerald-900/60 dark:bg-emerald-950/20">
                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-emerald-700 dark:text-emerald-200">Kode pembayaran</p>
                                <p class="mt-3 break-all text-2xl font-semibold text-zinc-950 dark:text-zinc-100">{{ $payCode }}</p>
                            </div>
                        @endif

                        @if ($paymentInstructions !== [])
                            <div class="mt-4 space-y-4">
                                @foreach ($paymentInstructions as $instruction)
                                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-900/60">
                                        <p class="font-semibold text-zinc-950 dark:text-zinc-100">{{ $instruction['title'] ?? 'Instruksi' }}</p>
                                        <ol class="mt-3 space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                                            @foreach (($instruction['steps'] ?? []) as $step)
                                                <li>{{ $loop->iteration }}. {{ $step }}</li>
                                            @endforeach
                                        </ol>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                @if ($transaction->fulfillment_message)
                    <div class="rounded-2xl border border-sky-200 bg-sky-50 px-5 py-4 text-sm text-sky-900 dark:border-sky-900/60 dark:bg-sky-950/30 dark:text-sky-200">
                        {{ $transaction->fulfillment_message }}
                    </div>
                @endif

                <details class="group rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                        <span>Rincian transaksi</span>
                        <span class="text-zinc-400 transition group-open:rotate-180">⌄</span>
                    </summary>

                    <dl class="mt-5 space-y-4 text-sm">
                        @if ($showPaymentReference)
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-zinc-500 dark:text-zinc-400">Referensi</dt>
                                <dd class="break-all text-right font-semibold text-zinc-950 dark:text-zinc-100">{{ $paymentReference }}</dd>
                            </div>
                        @endif

                        @if ($paymentOrderId)
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-zinc-500 dark:text-zinc-400">Order ID</dt>
                                <dd class="break-all text-right font-semibold text-zinc-950 dark:text-zinc-100">{{ $paymentOrderId }}</dd>
                            </div>
                        @endif

                        @if ($expiresAt)
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-zinc-500 dark:text-zinc-400">Jatuh tempo</dt>
                                <dd class="text-right font-semibold text-zinc-950 dark:text-zinc-100">{{ $expiresAt->translatedFormat('d M Y H:i') }}</dd>
                            </div>
                        @endif

                        @if ($transaction->paid_at)
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-zinc-500 dark:text-zinc-400">Dibayar pada</dt>
                                <dd class="text-right font-semibold text-zinc-950 dark:text-zinc-100">{{ \Illuminate\Support\Carbon::parse($transaction->paid_at)->translatedFormat('d M Y H:i') }}</dd>
                            </div>
                        @endif
                    </dl>
                </details>
            </div>

            <div class="space-y-6">
                <div class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
                    <h2 class="text-xl font-semibold text-zinc-950 dark:text-zinc-100">{{ $transaction->payment_status === 'unpaid' ? 'Lanjutkan pembayaran' : 'Pantau transaksi' }}</h2>

                    <div class="mt-5 space-y-3">
                        @if ($hasInlineMidtransPayment)
                            <button type="button" id="midtrans-pay-button" class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-emerald-500 dark:bg-emerald-500 dark:hover:bg-emerald-400">
                                Bayar sekarang
                            </button>
                        @endif

                        @if ($checkoutTarget && ! $hasInlineMidtransPayment && $transaction->payment_status === 'unpaid')
                            <a href="{{ $checkoutTarget }}" target="_blank" rel="noopener noreferrer" class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-emerald-500 dark:bg-emerald-500 dark:hover:bg-emerald-400">
                                Bayar sekarang
                            </a>
                        @endif

                        <form action="{{ route('ppob.transactions.refresh', $transaction) }}" method="POST">
                            @csrf
                            <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl border border-zinc-200 px-5 py-3 text-sm font-semibold text-zinc-800 transition hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-100 dark:hover:border-zinc-600 dark:hover:bg-zinc-950">
                                Refresh status
                            </button>
                        </form>
                    </div>

                    <a href="{{ route('home') }}" class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-emerald-600 hover:underline dark:text-emerald-400">
                        Buat transaksi lain
                    </a>
                </div>
            </div>
        </section>
    </div>
@endsection

@if ($hasInlineMidtransPayment)
    @push('head')
        <script src="{{ $midtransSnapScriptUrl }}" data-client-key="{{ $midtransClientKey }}"></script>
    @endpush

    @push('scripts')
        <script>
            document.getElementById('midtrans-pay-button')?.addEventListener('click', function () {
                window.snap.pay(@json($transaction->midtrans_snap_token), {
                    onSuccess: function () {
                        window.location.reload();
                    },
                    onPending: function () {
                        window.location.reload();
                    },
                    onError: function () {
                        window.location.reload();
                    }
                });
            });
        </script>
    @endpush
@endif
