<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Hapus Akun - {{ config('app.name', 'LAZ Al Azhar 5') }}</title>

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
                <h1 class="text-3xl font-bold mb-2">Hapus Akun</h1>
                <p class="text-zinc-500 dark:text-zinc-400 mb-8">Hapus akun Anda secara permanen</p>

                <div class="prose prose-zinc dark:prose-invert max-w-none">
                    {{-- Warning Section --}}
                    <section class="mb-8 bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 dark:border-yellow-600 p-6 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-yellow-800 dark:text-yellow-200 mb-2">Peringatan Penting</h3>
                                <p class="text-yellow-700 dark:text-yellow-300 mb-2">
                                    Tindakan ini akan menghapus akun Anda secara permanen. Mohon baca informasi berikut dengan seksama:
                                </p>
                                <ul class="list-disc list-inside text-yellow-700 dark:text-yellow-300 space-y-1">
                                    <li>Akun yang dihapus akan dinonaktifkan secara permanen</li>
                                    <li>Anda tidak akan dapat mengakses data akun setelah dihapus</li>
                                    <li>Data donasi dan riwayat transaksi akan tetap tersimpan di sistem</li>
                                    <li>Tindakan ini tidak dapat dibatalkan</li>
                                </ul>
                            </div>
                        </div>
                    </section>

                    {{-- Form Section --}}
                    <section class="mb-8">
                        <h2 class="text-xl font-semibold mb-4 text-zinc-900 dark:text-zinc-100">Formulir Penghapusan Akun</h2>
                        <p class="text-zinc-600 dark:text-zinc-400 mb-6">
                            Masukkan nomor telepon yang terdaftar pada akun Anda untuk menghapus akun.
                        </p>

                        <form id="deleteAccountForm" class="space-y-4">
                            <div>
                                <label for="phone" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                    Nomor Telepon
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                        </svg>
                                    </div>
                                    <input
                                        type="tel"
                                        id="phone"
                                        name="phone"
                                        required
                                        autofocus
                                        class="block w-full pl-10 pr-3 py-3 border border-zinc-300 dark:border-zinc-700 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 dark:bg-zinc-900 dark:text-white sm:text-sm"
                                        placeholder="08123456789"
                                    >
                                </div>
                                <p id="phoneError" class="mt-2 text-sm text-red-600 dark:text-red-400 hidden"></p>
                            </div>

                            <button
                                type="submit"
                                id="submitBtn"
                                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            >
                                Hapus Akun
                            </button>
                        </form>

                        <div id="messageContainer" class="mt-6 hidden">
                            <div id="messageContent" class="rounded-lg p-4"></div>
                        </div>
                    </section>

                    {{-- Information Section --}}
                    <section class="mb-8">
                        <h2 class="text-xl font-semibold mb-4 text-zinc-900 dark:text-zinc-100">Informasi Tambahan</h2>
                        <div class="text-zinc-600 dark:text-zinc-400 space-y-4">
                            <p>
                                Jika Anda mengalami masalah atau memiliki pertanyaan seputar penghapusan akun, Anda dapat menghubungi kami melalui:
                            </p>
                            <ul class="list-none space-y-2">
                                <li><strong>Email:</strong> info@lazalazhar5.com</li>
                                <li><strong>WhatsApp:</strong> +62 812-3456-7890</li>
                            </ul>
                        </div>
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

        <script>
            const form = document.getElementById('deleteAccountForm');
            const phoneInput = document.getElementById('phone');
            const submitBtn = document.getElementById('submitBtn');
            const phoneError = document.getElementById('phoneError');
            const messageContainer = document.getElementById('messageContainer');
            const messageContent = document.getElementById('messageContent');

            function validatePhone(phone) {
                const regex = /^(?:\+62|62|0)8[1-9][0-9]{6,11}$/;
                return regex.test(phone);
            }

            function showError(message) {
                phoneError.textContent = message;
                phoneError.classList.remove('hidden');
            }

            function hideError() {
                phoneError.classList.add('hidden');
            }

            function showMessage(type, message) {
                messageContainer.classList.remove('hidden');
                if (type === 'success') {
                    messageContent.className = 'rounded-lg p-4 bg-green-50 dark:bg-green-900/20 text-green-800 dark:text-green-200';
                } else {
                    messageContent.className = 'rounded-lg p-4 bg-red-50 dark:bg-red-900/20 text-red-800 dark:text-red-200';
                }
                messageContent.textContent = message;
            }

            function hideMessage() {
                messageContainer.classList.add('hidden');
            }

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                hideError();
                hideMessage();

                const phone = phoneInput.value.trim();

                if (!validatePhone(phone)) {
                    showError('Format nomor telepon harus dimulai dengan 08, 628, atau +628');
                    return;
                }

                const confirmed = confirm('Anda yakin ingin menghapus akun?\n\nTindakan ini tidak dapat dibatalkan. Akun Anda akan dinonaktifkan secara permanen.');
                if (!confirmed) {
                    return;
                }

                submitBtn.disabled = true;
                submitBtn.textContent = 'Memproses...';

                try {
                    const response = await fetch('/api/account-deletion', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ phone }),
                    });

                    const data = await response.json();

                    if (response.ok) {
                        showMessage('success', data.message || 'Akun berhasil dihapus.');
                        form.reset();
                    } else {
                        showMessage('error', data.message || 'Terjadi kesalahan. Silakan coba lagi.');
                    }
                } catch (error) {
                    showMessage('error', 'Terjadi kesalahan jaringan. Silakan coba lagi.');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Hapus Akun';
                }
            });

            phoneInput.addEventListener('input', () => {
                hideError();
                hideMessage();
            });
        </script>
    </body>
</html>
