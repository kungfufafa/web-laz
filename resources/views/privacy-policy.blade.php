<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Kebijakan Privasi - {{ config('app.name', 'LAZ Al Azhar 5') }}</title>

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
                <h1 class="text-3xl font-bold mb-2">Kebijakan Privasi</h1>
                <p class="text-zinc-500 dark:text-zinc-400 mb-8">Terakhir diperbarui: {{ date('d F Y') }}</p>

                <div class="prose prose-zinc dark:prose-invert max-w-none">
                    <section class="mb-8">
                        <h2 class="text-xl font-semibold mb-4 text-zinc-900 dark:text-zinc-100">1. Pendahuluan</h2>
                        <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed mb-4">
                            Selamat datang di aplikasi LAZ Al Azhar 5. Kami menghargai privasi Anda dan berkomitmen untuk melindungi data pribadi yang Anda berikan kepada kami. Kebijakan Privasi ini menjelaskan bagaimana kami mengumpulkan, menggunakan, dan melindungi informasi Anda.
                        </p>
                        <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed">
                            Dengan menggunakan aplikasi kami, Anda menyetujui pengumpulan dan penggunaan informasi sesuai dengan kebijakan ini.
                        </p>
                    </section>

                    <section class="mb-8">
                        <h2 class="text-xl font-semibold mb-4 text-zinc-900 dark:text-zinc-100">2. Informasi yang Kami Kumpulkan</h2>
                        <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed mb-4">
                            Kami mengumpulkan informasi berikut saat Anda menggunakan aplikasi:
                        </p>
                        <ul class="list-disc list-inside text-zinc-600 dark:text-zinc-400 space-y-2 ml-4">
                            <li><strong>Informasi Akun:</strong> Nama lengkap, alamat email, dan nomor telepon saat Anda mendaftar.</li>
                            <li><strong>Data Donasi:</strong> Riwayat donasi, jumlah donasi, jenis donasi (zakat, infak, sedekah), dan metode pembayaran.</li>
                            <li><strong>Bukti Transfer:</strong> Gambar bukti transfer yang Anda unggah untuk verifikasi donasi.</li>
                            <li><strong>Data Lokasi:</strong> Lokasi approximate untuk menampilkan jadwal sholat sesuai wilayah Anda (dengan izin Anda).</li>
                        </ul>
                    </section>

                    <section class="mb-8">
                        <h2 class="text-xl font-semibold mb-4 text-zinc-900 dark:text-zinc-100">3. Penggunaan Informasi</h2>
                        <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed mb-4">
                            Informasi yang kami kumpulkan digunakan untuk:
                        </p>
                        <ul class="list-disc list-inside text-zinc-600 dark:text-zinc-400 space-y-2 ml-4">
                            <li>Memproses dan mencatat donasi Anda</li>
                            <li>Memberikan layanan jadwal sholat berdasarkan lokasi</li>
                            <li>Mengirim notifikasi pengingat</li>
                            <li>Menghubungi Anda terkait status donasi atau informasi penting</li>
                            <li>Meningkatkan kualitas layanan aplikasi</li>
                        </ul>
                    </section>

                    <section class="mb-8">
                        <h2 class="text-xl font-semibold mb-4 text-zinc-900 dark:text-zinc-100">4. Keamanan Data</h2>
                        <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed">
                            Kami menerapkan langkah-langkah keamanan untuk melindungi informasi pribadi Anda, termasuk enkripsi data saat transmisi, penyimpanan yang aman, dan pembatasan akses kepada personel yang berwenang saja.
                        </p>
                    </section>

                    <section class="mb-8">
                        <h2 class="text-xl font-semibold mb-4 text-zinc-900 dark:text-zinc-100">5. Hak Anda</h2>
                        <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed mb-4">
                            Anda memiliki hak untuk:
                        </p>
                        <ul class="list-disc list-inside text-zinc-600 dark:text-zinc-400 space-y-2 ml-4">
                            <li>Mengakses data pribadi yang kami simpan tentang Anda</li>
                            <li>Meminta koreksi data yang tidak akurat</li>
                            <li>Meminta penghapusan akun melalui email</li>
                            <li>Menolak notifikasi pemasaran</li>
                        </ul>
                    </section>

                    <section class="mb-8">
                        <h2 class="text-xl font-semibold mb-4 text-zinc-900 dark:text-zinc-100">6. Perubahan Kebijakan</h2>
                        <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed">
                            Kami dapat memperbarui Kebijakan Privasi ini dari waktu ke waktu. Perubahan akan diumumkan melalui aplikasi. Penggunaan berkelanjutan setelah perubahan berarti Anda menerima kebijakan yang diperbarui.
                        </p>
                    </section>

                    <section class="mb-8">
                        <h2 class="text-xl font-semibold mb-4 text-zinc-900 dark:text-zinc-100">7. Hubungi Kami</h2>
                        <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed">
                            Jika Anda memiliki pertanyaan tentang Kebijakan Privasi ini:
                        </p>
                        <ul class="list-none text-zinc-600 dark:text-zinc-400 space-y-2 mt-4">
                            <li><strong>Email:</strong> info@lazalazhar5.com</li>
                            <li><strong>WhatsApp:</strong> +62 838-3946-3566</li>
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
