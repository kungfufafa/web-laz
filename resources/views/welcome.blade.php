<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'LAZ Al Azhar 5') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-white dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 min-h-screen flex flex-col">

        {{-- Header --}}
        <header class="w-full border-b border-zinc-200 dark:border-zinc-800">
            <div class="mx-auto max-w-5xl flex items-center justify-between px-6 py-4">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logo.png') }}" alt="LAZ Al Azhar 5" class="h-10 w-10 rounded-full">
                    <span class="text-lg font-semibold">LAZ Al Azhar 5</span>
                </div>

                @if (Route::has('login'))
                    <nav class="flex items-center gap-3">
                        @auth
                            <a href="{{ url('/admin') }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 transition-colors">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="rounded-lg border border-zinc-300 dark:border-zinc-700 px-4 py-2 text-sm font-medium hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
                                Masuk
                            </a>
                        @endauth
                    </nav>
                @endif
            </div>
        </header>

        {{-- Hero --}}
        <main class="flex-1 flex flex-col">
            <section class="flex-1 flex items-center justify-center px-6 py-16 lg:py-24">
                <div class="mx-auto max-w-2xl text-center">
                    <img src="{{ asset('images/logo.png') }}" alt="LAZ Al Azhar 5" class="mx-auto mb-8 h-28 w-28 rounded-2xl shadow-lg">

                    <h1 class="text-4xl font-bold tracking-tight lg:text-5xl">
                        LAZ <span class="text-emerald-600 dark:text-emerald-400">Al Azhar 5</span>
                    </h1>

                    <p class="mt-4 text-lg text-zinc-600 dark:text-zinc-400 leading-relaxed">
                        Tunaikan zakat, infak, dan sedekah dengan mudah melalui aplikasi kami.
                        <br>
                        Download sekarang dan mulai berdonasi.
                    </p>

                    <div class="mt-10 flex flex-col gap-4 sm:flex-row sm:justify-center">
                        <a href="https://play.google.com/store/apps/details?id=com.mekayastudio.baitulmaal" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-3 rounded-xl bg-zinc-900 dark:bg-white px-6 py-3 text-sm font-semibold text-white dark:text-zinc-900 shadow-sm hover:bg-zinc-800 dark:hover:bg-zinc-100 transition-colors">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M3.609 1.814L13.792 12 3.61 22.186a.996.996 0 0 1-.61-.92V2.734a1 1 0 0 1 .609-.92zm10.89 10.893l2.302 2.302-10.937 6.333 8.635-8.635zm3.199-3.199l2.807 1.626a1 1 0 0 1 0 1.732l-2.807 1.626L15.206 12l2.492-2.492zM5.864 3.455L16.8 9.788l-2.302 2.302-8.634-8.635z"/>
                            </svg>
                            <span class="text-left">
                                <span class="block text-xs font-normal opacity-75">Download di</span>
                                <span class="block text-base font-semibold leading-tight">Google Play</span>
                            </span>
                        </a>
                        <a href="https://apps.apple.com/app/com.mekayastudio.baitulmaal" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-3 rounded-xl bg-zinc-900 dark:bg-white px-6 py-3 text-sm font-semibold text-white dark:text-zinc-900 shadow-sm hover:bg-zinc-800 dark:hover:bg-zinc-100 transition-colors">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.8-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
                            </svg>
                            <span class="text-left">
                                <span class="block text-xs font-normal opacity-75">Download di</span>
                                <span class="block text-base font-semibold leading-tight">App Store</span>
                            </span>
                        </a>
                    </div>
                </div>
            </section>

            {{-- Features --}}
            <section class="border-t border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900 px-6 py-16">
                <div class="mx-auto max-w-5xl">
                    <h2 class="text-center text-2xl font-bold mb-12">Layanan Kami</h2>
                    <div class="grid grid-cols-1 gap-8 md:grid-cols-3">

                        <div class="rounded-xl bg-white dark:bg-zinc-800 p-6 shadow-sm border border-zinc-200 dark:border-zinc-700">
                            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold mb-2">Zakat</h3>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                Kalkulator zakat fitrah, maal, dan profesi untuk membantu menghitung kewajiban zakat Anda.
                            </p>
                        </div>

                        <div class="rounded-xl bg-white dark:bg-zinc-800 p-6 shadow-sm border border-zinc-200 dark:border-zinc-700">
                            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold mb-2">Infak & Sedekah</h3>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                Salurkan infak dan sedekah untuk pendidikan, kemanusiaan, dan operasional dakwah.
                            </p>
                        </div>

                        <div class="rounded-xl bg-white dark:bg-zinc-800 p-6 shadow-sm border border-zinc-200 dark:border-zinc-700">
                            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-sky-100 dark:bg-sky-900/50 text-sky-600 dark:text-sky-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold mb-2">Konten Islami</h3>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                Artikel kajian dan video dakwah untuk menambah wawasan keislaman.
                            </p>
                        </div>

                    </div>
                </div>
            </section>
        </main>

        {{-- Footer --}}
        <footer class="border-t border-zinc-200 dark:border-zinc-800 px-6 py-8">
            <div class="mx-auto max-w-5xl flex flex-col items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                <div class="flex items-center gap-2">
                    <img src="{{ asset('images/logo.png') }}" alt="LAZ Al Azhar 5" class="h-6 w-6 rounded-full">
                    <span class="font-medium text-zinc-700 dark:text-zinc-300">Lembaga Amil Zakat Al Azhar 5</span>
                </div>
                <p>&copy; {{ date('Y') }} LAZ Al Azhar 5. Amanah dalam pengelolaan dana umat.</p>
            </div>
        </footer>

    </body>
</html>
