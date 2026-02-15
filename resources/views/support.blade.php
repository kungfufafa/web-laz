<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Bantuan - {{ config('app.name', 'LAZ Al Azhar 5') }}</title>

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
                <h1 class="text-3xl font-bold mb-2">Pusat Bantuan</h1>
                <p class="text-zinc-500 dark:text-zinc-400 mb-8">Temukan jawaban untuk pertanyaan umum atau hubungi kami</p>

                {{-- FAQ Section --}}
                <section class="mb-12">
                    <h2 class="text-xl font-semibold mb-6 text-zinc-900 dark:text-zinc-100">Pertanyaan Umum (FAQ)</h2>

                    <div class="space-y-4">
                        <div class="rounded-xl bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-6">
                            <h3 class="font-semibold text-zinc-900 dark:text-zinc-100 mb-2">Bagaimana cara mendaftar akun?</h3>
                            <p class="text-zinc-600 dark:text-zinc-400 text-sm leading-relaxed">
                                Buka aplikasi LAZ Al Azhar 5, klik tombol "Masuk / Daftar" di beranda, lalu pilih "Daftar". Isi nama lengkap, email, nomor HP, dan password untuk membuat akun baru.
                            </p>
                        </div>

                        <div class="rounded-xl bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-6">
                            <h3 class="font-semibold text-zinc-900 dark:text-zinc-100 mb-2">Bagaimana cara berdonasi?</h3>
                            <p class="text-zinc-600 dark:text-zinc-400 text-sm leading-relaxed">
                                Pilih menu "Donasi" di tab bawah, pilih jenis donasi (Zakat/Infak/Sedekah), masukkan nominal, pilih metode pembayaran, lalu ikuti langkah-langkah selanjutnya hingga upload bukti transfer.
                            </p>
                        </div>

                        <div class="rounded-xl bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-6">
                            <h3 class="font-semibold text-zinc-900 dark:text-zinc-100 mb-2">Apakah saya bisa donasi tanpa login?</h3>
                            <p class="text-zinc-600 dark:text-zinc-400 text-sm leading-relaxed">
                                Ya, Anda bisa berdonasi sebagai tamu (guest). Namun, Anda perlu mengisi nama dan nomor WhatsApp untuk keperluan verifikasi dan konfirmasi donasi.
                            </p>
                        </div>

                        <div class="rounded-xl bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-6">
                            <h3 class="font-semibold text-zinc-900 dark:text-zinc-100 mb-2">Bagaimana cara menghitung zakat?</h3>
                            <p class="text-zinc-600 dark:text-zinc-400 text-sm leading-relaxed">
                                Di menu Donasi, pilih kategori "Zakat". Anda akan diarahkan ke kalkulator zakat sesuai jenisnya (Fitrah, Maal, atau Profesi). Isi data yang diminta dan sistem akan menghitung nominal zakat Anda secara otomatis.
                            </p>
                        </div>

                        <div class="rounded-xl bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-6">
                            <h3 class="font-semibold text-zinc-900 dark:text-zinc-100 mb-2">Metode pembayaran apa saja yang tersedia?</h3>
                            <p class="text-zinc-600 dark:text-zinc-400 text-sm leading-relaxed">
                                Kami menyediakan berbagai metode pembayaran termasuk transfer bank (BSI, Mandiri, BNI), QRIS, dan e-wallet. Pilih metode yang paling nyaman untuk Anda saat proses donasi.
                            </p>
                        </div>

                        <div class="rounded-xl bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-6">
                            <h3 class="font-semibold text-zinc-900 dark:text-zinc-100 mb-2">Bagaimana cara melihat riwayat donasi?</h3>
                            <p class="text-zinc-600 dark:text-zinc-400 text-sm leading-relaxed">
                                Buka tab "Riwayat" di menu bawah aplikasi. Di sana Anda dapat melihat semua donasi yang pernah Anda lakukan beserta statusnya (pending, terverifikasi, dll).
                            </p>
                        </div>

                        <div class="rounded-xl bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-6">
                            <h3 class="font-semibold text-zinc-900 dark:text-zinc-100 mb-2">Kenapa donasi saya masih pending?</h3>
                            <p class="text-zinc-600 dark:text-zinc-400 text-sm leading-relaxed">
                                Status pending berarti donasi Anda sedang menunggu verifikasi dari tim kami. Proses verifikasi biasanya memakan waktu 1-3 hari kerja. Pastikan bukti transfer yang diupload jelas dan sesuai.
                            </p>
                        </div>

                        <div class="rounded-xl bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-6">
                            <h3 class="font-semibold text-zinc-900 dark:text-zinc-100 mb-2">Bagaimana cara menghapus akun?</h3>
                            <p class="text-zinc-600 dark:text-zinc-400 text-sm leading-relaxed">
                                Untuk menghapus akun, silakan hubungi tim support kami melalui email di info@rizqis.com dengan subjek "Permintaan Hapus Akun". Sertakan email akun yang ingin dihapus.
                            </p>
                        </div>
                    </div>
                </section>

                {{-- Contact Section --}}
                <section class="mb-12">
                    <h2 class="text-xl font-semibold mb-6 text-zinc-900 dark:text-zinc-100">Hubungi Kami</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 p-6">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <h3 class="font-semibold text-zinc-900 dark:text-zinc-100">Email</h3>
                            </div>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-2">Kirim pertanyaan atau keluhan Anda</p>
                            <a href="mailto:info@rizqis.com" class="text-emerald-600 dark:text-emerald-400 font-medium hover:underline">info@rizqis.com</a>
                        </div>

                        <div class="rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 p-6">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                </div>
                                <h3 class="font-semibold text-zinc-900 dark:text-zinc-100">WhatsApp</h3>
                            </div>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-2">Chat langsung dengan tim support</p>
                            <a href="https://wa.me/6281234567890" target="_blank" rel="noopener noreferrer" class="text-emerald-600 dark:text-emerald-400 font-medium hover:underline">+62 812-3456-7890</a>
                        </div>
                    </div>
                </section>

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
