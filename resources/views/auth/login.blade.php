@php($title = 'Masuk Web PPOB')

@extends('layouts.web')

@section('content')
    <div class="mx-auto grid max-w-5xl gap-6 lg:grid-cols-[minmax(0,1fr)_24rem]">
        <section class="rounded-2xl border border-zinc-200 bg-white p-8 dark:border-zinc-800 dark:bg-zinc-900">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-emerald-600 dark:text-emerald-400">Lanjutkan PPOB</p>
            <h1 class="mt-3 max-w-2xl text-3xl font-bold tracking-tight text-zinc-950 dark:text-zinc-100 sm:text-4xl">
                Masuk untuk lanjut pilih produk dan bayar.
            </h1>
            <p class="mt-3 max-w-2xl text-sm leading-7 text-zinc-600 dark:text-zinc-400">
                Flow web ini dibuat sesingkat katalog PPOB, jadi setelah masuk Anda langsung kembali ke alur transaksi.
            </p>

            <div class="mt-8 flex flex-wrap gap-3">
                <span class="inline-flex items-center rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-950/20 dark:text-emerald-200">Pulsa</span>
                <span class="inline-flex items-center rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-950/20 dark:text-emerald-200">Data</span>
                <span class="inline-flex items-center rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-950/20 dark:text-emerald-200">Token PLN</span>
                <span class="inline-flex items-center rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-950/20 dark:text-emerald-200">Tagihan</span>
            </div>
        </section>

        <section class="rounded-2xl border border-zinc-200 bg-white p-6 sm:p-8 dark:border-zinc-800 dark:bg-zinc-900">
            <h2 class="text-2xl font-bold tracking-tight text-zinc-950 dark:text-zinc-100">Masuk</h2>
            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Nomor telepon akan dinormalisasi otomatis ke format Indonesia.</p>

            <form action="{{ route('login.store') }}" method="POST" class="mt-8 space-y-5">
                @csrf
                <div class="space-y-2">
                    <label for="phone" class="text-sm font-semibold text-zinc-700 dark:text-zinc-200">Nomor telepon</label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone') }}" placeholder="081234567890" class="w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-emerald-400 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:focus:border-emerald-500">
                </div>

                <div class="space-y-2">
                    <label for="password" class="text-sm font-semibold text-zinc-700 dark:text-zinc-200">Kata sandi</label>
                    <input id="password" name="password" type="password" placeholder="Masukkan kata sandi" class="w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-emerald-400 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:focus:border-emerald-500">
                </div>

                <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-emerald-500 dark:bg-emerald-500 dark:hover:bg-emerald-400">
                    Masuk ke PPOB Web
                </button>
            </form>

            <p class="mt-6 text-sm text-zinc-500 dark:text-zinc-400">
                Belum punya akun?
                <a href="{{ route('register') }}" class="font-semibold text-emerald-700 hover:underline dark:text-emerald-400">Daftar di sini</a>
            </p>
        </section>
    </div>
@endsection
