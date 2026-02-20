<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hapus Akun - LAZ</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="min-h-screen flex items-center justify-center px-4 py-12">
        <div class="max-w-md w-full">
            <div class="bg-white rounded-lg shadow-md p-8">
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Hapus Akun</h1>
                <p class="text-gray-600 mb-6">
                    Masukkan nomor telepon yang terdaftar untuk menghapus akun Anda.
                </p>

                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <strong>Peringatan:</strong> Akun yang dihapus akan dinonaktifkan. Tindakan ini tidak dapat dibatalkan.
                            </p>
                        </div>
                    </div>
                </div>

                <form id="deleteAccountForm" class="space-y-4">
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                            Nomor Telepon
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            </div>
                            <input
                                type="tel"
                                id="phone"
                                name="phone"
                                required
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm"
                                placeholder="08123456789"
                            >
                        </div>
                        <p id="phoneError" class="mt-1 text-sm text-red-600 hidden"></p>
                    </div>

                    <button
                        type="submit"
                        id="submitBtn"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Hapus Akun
                    </button>
                </form>

                <div id="messageContainer" class="mt-4 hidden">
                    <div id="messageContent" class="rounded-md p-4"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('deleteAccountForm');
        const phoneInput = document.getElementById('phone');
        const submitBtn = document.getElementById('submitBtn');
        const phoneError = document.getElementById('phoneError');
        const messageContainer = document.getElementById('messageContainer');
        const messageContent = document.getElementById('messageContent');

        function validatePhone(phone) {
            const regex = /^(?:\+62|62|0)8[1-9][0-9]{6,9}$/;
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
            messageContent.className = 'rounded-md p-4 ' + (type === 'success' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800');
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

            const confirmed = confirm('Anda yakin ingin menghapus akun?\n\nTindakan ini tidak dapat dibatalkan.');
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
                    showMessage('success', data.message);
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
