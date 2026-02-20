<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Syarat & Ketentuan - {{ config('app.name', 'LAZ Al Azhar 5') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-white dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 min-h-screen flex flex-col">

        {{-- Header --}}
        <header class="w-full border-b border-zinc-200 dark:border-zinc-800">
            <div class="mx-auto max-w-5xl flex items-center justify-between px-6 py-4">
                <a href="{{ url('/') }}" class="flex items-center gap-3 hover:opacity-80 transition-opacity">
                    <img src="{{ asset('images/logo.png') }}" alt="LAZ Al Azhar 5" class="h-10 w-10 rounded-full">
                    <span class="text-lg font-semibold">LAZ Al Azhar 5</span>
                </a>

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

        {{-- Content --}}
        <main class="flex-1 px-6 py-12">
            <div class="mx-auto max-w-3xl">
                <h1 class="text-3xl font-bold mb-2">Syarat & Ketentuan</h1>
                <p class="text-zinc-500 dark:text-zinc-400 mb-8">Terakhir diperbarui: {{ date('d F Y') }}</p>

                <div class="prose prose-zinc dark:prose-invert max-w-none">
                    <section class="mb-8">
                        <h2 class="text-xl font-semibold mb-4 text-zinc-900 dark:text-zinc-100">1. Ketentuan Umum</h2>
                        <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed mb-4">
                            Dengan mengunduh, mengakses, atau menggunakan aplikasi LAZ Al Azhar 5, Anda menyetujui untuk terikat dengan Syarat & Ketentuan ini. Jika Anda tidak menyetujui ketentuan ini, mohon untuk tidak menggunakan aplikasi kami.
                        </p>
                        <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed">
                            Kami berhak untuk mengubah Syarat & Ketentuan ini kapan saja. Perubahan akan berlaku efektif segera setelah dipublikasikan di aplikasi.
                        </p>
                    </section>

                    <section class="mb-8">
                        <h2 class="text-xl font-semibold mb-4 text-zinc-900 dark:text-zinc-100">2. Layanan Aplikasi</h2>
                        <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed mb-4">
                            Aplikasi LAZ Al Azhar 5 menyediakan layanan berikut:
                        </p>
                        <ul class="list-disc list-inside text-zinc-600 dark:text-zinc-400 space-y-2 ml-4">
                            <li>Penyaluran zakat (fitrah, maal, profesi)</li>
                            <li>Penyaluran infak dan sedekah</li>
                            <li>Kalkulator zakat untuk membantu perhitungan</li>
                            <li>Jadwal sholat berdasarkan lokasi</li>
                            <li>Konten islami (artikel dan video dakwah)</li>
                            <li>Doa harian dan tracking baca Al-Quran</li>
                            <li>Riwayat dan tracking donasi</li>
                        </ul>
                    </section>

                    <section class="mb-8">
                        <h2 class="text-xl font-semibold mb-4 text-zinc-900 dark:text-zinc-100">3. Akun Pengguna</h2>
                        <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed mb-4">
                            Untuk menggunakan fitur donasi dan menyimpan data, Anda perlu membuat akun dengan:
                        </p>
                        <ul class="list-disc list-inside text-zinc-600 dark:text-zinc-400 space-y-2 ml-4">
                            <li>Memberikan informasi yang akurat dan lengkap</li>
                            <li>Menjaga kerahasiaan kata sandi akun Anda</li>
                            <li>Tidak menggunakan akun untuk aktivitas ilegal atau penipuan</li>
                            <li>Tidak mentransfer akun Anda kepada pihak lain</li>
                        </ul>
                        <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed mt-4">
                            Anda bertanggung jawab penuh atas semua aktivitas yang terjadi di akun Anda.
                        </p>
                    </section>

                    <section class="mb-8">
                        <h2 class="text-xl font-semibold mb-4 text-zinc-900 dark:text-zinc-100">4. Donasi dan Pembayaran</h2>
                        <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed mb-4">
                            Ketika melakukan donasi melalui aplikasi:
                        </p>
                        <ul class="list-disc list-inside text-zinc-600 dark:text-zinc-400 space-y-2 ml-4">
                            <li>Anda menjamin bahwa dana yang didonasikan adalah milik Anda secara sah</li>
                            <li>Donasi yang telah dikonfirmasi tidak dapat dibatalkan atau dikembalikan</li>
                            <li>Anda wajib mengunggah bukti transfer yang valid untuk verifikasi</li>
                            <li>LAZ Al Azhar 5 berhak memverifikasi setiap donasi</li>
                            <li>Status donasi akan diperbarui setelah proses verifikasi selesai</li>
                        </ul>
                    </section>

                    <section class="mb-8">
                        <h2 class="text-xl font-semibold mb-4 text-zinc-900 dark:text-zinc-100">5. Larangan Penggunaan</h2>
                        <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed mb-4">
                            Anda dilarang untuk:
                        </p>
                        <ul class="list-disc list-inside text-zinc-600 dark:text-zinc-400 space-y-2 ml-4">
                            <li>Menggunakan aplikasi untuk tujuan ilegal atau penipuan</li>
                            <li>Mencoba mengakses sistem kami secara tidak sah</li>
                            <li>Mengganggu atau merusak operasional aplikasi</li>
                            <li>Menyebarkan malware atau kode berbahaya</li>
                            <li>Melakukan donasi menggunakan dana hasil kejahatan</li>
                            <li>Menyalahgunakan fitur aplikasi untuk keuntungan pribadi</li>
                        </ul>
                    </section>

                    <section class="mb-8">
                        <h2 class="text-xl font-semibold mb-4 text-zinc-900 dark:text-zinc-100">6. Batasan Tanggung Jawab</h2>
                        <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed mb-4">
                            LAZ Al Azhar 5 tidak bertanggung jawab atas kerugian yang timbul dari:
                        </p>
                        <ul class="list-disc list-inside text-zinc-600 dark:text-zinc-400 space-y-2 ml-4">
                            <li>Kesalahan pengguna dalam memasukkan data donasi</li>
                            <li>Gangguan jaringan atau kegagalan sistem pembayaran</li>
                            <li>Penggunaan aplikasi yang tidak sesuai dengan ketentuan</li>
                            <li>Akses tidak sah ke akun pengguna</li>
                        </ul>
                    </section>

                    <section class="mb-8">
                        <h2 class="text-xl font-semibold mb-4 text-zinc-900 dark:text-zinc-100">7. Hukum yang Berlaku</h2>
                        <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed">
                            Syarat & Ketentuan ini diatur sesuai dengan hukum Republik Indonesia. Setiap sengketa akan diselesaikan melalui musyawarah, dan jika tidak tercapai kesepakatan, akan diselesaikan melalui pengadilan yang berwenang di Indonesia.
                        </p>
                    </section>

                    <section class="mb-8">
                        <h2 class="text-xl font-semibold mb-4 text-zinc-900 dark:text-zinc-100">8. Hubungi Kami</h2>
                        <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed">
                            Untuk pertanyaan tentang Syarat & Ketentuan ini:
                        </p>
                        <ul class="list-none text-zinc-600 dark:text-zinc-400 space-y-2 mt-4">
                            <li><strong>Email:</strong> info@lazalazhar5.com</li>
                            <li><strong>WhatsApp:</strong> +62 812-3456-7890</li>
                        </ul>
                    </section>
                </div>

                <div class="mt-12 pt-8 border-t border-zinc-200 dark:border-zinc-800">
                    <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-emerald-600 dark:text-emerald-400 hover:underline">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali ke Beranda
                    </a>
                </div>
            </div>
        </main>

        {{-- Footer --}}
        <footer class="border-t border-zinc-200 dark:border-zinc-800 px-6 py-8">
            <div class="mx-auto max-w-5xl flex flex-col items-center gap-4 text-sm text-zinc-500 dark:text-zinc-400">
                <div class="flex flex-wrap justify-center gap-6">
                    <a href="{{ url('/privacy-policy') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">Kebijakan Privasi</a>
                    <a href="{{ url('/terms-of-service') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">Syarat & Ketentuan</a>
                    <a href="{{ url('/support') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">Bantuan</a>
                </div>
                <div class="flex items-center gap-2">
                    <img src="{{ asset('images/logo.png') }}" alt="LAZ Al Azhar 5" class="h-6 w-6 rounded-full">
                    <span class="font-medium text-zinc-700 dark:text-zinc-300">Lembaga Amil Zakat Al Azhar 5</span>
                </div>
                <p>&copy; {{ date('Y') }} LAZ Al Azhar 5. Amanah dalam pengelolaan dana umat.</p>
            </div>
        </footer>

    </body>
</html>
