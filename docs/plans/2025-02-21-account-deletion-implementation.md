# Account Deletion Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Build account deletion feature for LAZ-WEB with phone validation and soft delete functionality

**Architecture:** Public API endpoint with rate limiting for account deletion, combined with Blade view frontend form. Uses existing soft delete functionality in User model and follows Laravel 12 + Pest testing conventions.

**Tech Stack:** Laravel 12, Pest 4, Tailwind CSS 4, Sanctum 4

---

## Task 1: Add API Endpoint Route

**Files:**
- Modify: `routes/api.php`

**Step 1: Add route for account deletion**

Add this line to `routes/api.php` after the auth routes (around line 16):

```php
Route::middleware('throttle:5,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/account-deletion', [AuthController::class, 'deleteAccount']); // ADD THIS
});
```

**Step 2: Commit**

```bash
git add routes/api.php
git commit -m "feat: add account deletion route with rate limiting"
```

---

## Task 2: Implement Controller Method

**Files:**
- Modify: `app/Http/Controllers/Api/AuthController.php`

**Step 1: Add deleteAccount method**

Add this method to `AuthController` class after the `logout` method:

```php
public function deleteAccount(Request $request)
{
    $validated = $request->validate([
        'phone' => ['required', 'string', 'regex:/^(?:\+62|62|0)8[1-9][0-9]{6,9}$/'],
    ], [
        'phone.regex' => 'Format nomor telepon harus dimulai dengan 08, 628, atau +628',
    ]);

    // Normalize phone number for database lookup
    $phone = $validated['phone'];
    if (str_starts_with($phone, '+62')) {
        $phone = '0' . substr($phone, 3);
    } elseif (str_starts_with($phone, '62')) {
        $phone = '0' . substr($phone, 2);
    }

    $user = User::where('phone', $phone)->first();

    if (! $user) {
        return response()->json([
            'message' => 'Akun dengan nomor telepon tersebut tidak ditemukan',
        ], 404);
    }

    $user->delete();

    return response()->json([
        'message' => 'Akun berhasil dihapus',
    ]);
}
```

**Step 2: Commit**

```bash
git add app/Http/Controllers/Api/AuthController.php
git commit -m "feat: implement deleteAccount method with phone validation"
```

---

## Task 3: Add Web Route for Frontend Page

**Files:**
- Modify: `routes/web.php`

**Step 1: Add account deletion route**

Add this route after the `/support` route (around line 19):

```php
Route::get('/account-deletion', function () {
    return view('account-deletion');
})->name('account-deletion');
```

**Step 2: Commit**

```bash
git add routes/web.php
git commit -m "feat: add web route for account deletion page"
```

---

## Task 4: Create Blade View

**Files:**
- Create: `resources/views/account-deletion.blade.php`

**Step 1: Create the Blade view**

Create complete view file with Tailwind CSS styling:

```blade
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
```

**Step 2: Commit**

```bash
git add resources/views/account-deletion.blade.php
git commit -m "feat: create account deletion page with form and validation"
```

---

## Task 5: Write Feature Test for API Endpoint

**Files:**
- Create: `tests/Feature/AccountDeletionTest.php`

**Step 1: Create the feature test**

Run artisan command:

```bash
php artisan make:test --pest AccountDeletion
```

**Step 2: Write test cases**

Open `tests/Feature/AccountDeletionTest.php` and add:

```php
<?php

use App\Models\User;

test('account deletion succeeds with valid phone', function () {
    $user = User::factory()->create([
        'phone' => '081234567890',
    ]);

    $response = $this->postJson('/api/account-deletion', [
        'phone' => '081234567890',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Akun berhasil dihapus',
        ]);

    $this->assertSoftDeleted('users', [
        'id' => $user->id,
    ]);
});

test('account deletion fails with invalid phone format', function () {
    $response = $this->postJson('/api/account-deletion', [
        'phone' => '123456789',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['phone']);
});

test('account deletion fails when phone not found', function () {
    $response = $this->postJson('/api/account-deletion', [
        'phone' => '089876543210',
    ]);

    $response->assertStatus(404)
        ->assertJson([
            'message' => 'Akun dengan nomor telepon tersebut tidak ditemukan',
        ]);
});

test('account deletion works with +62 prefix', function () {
    $user = User::factory()->create([
        'phone' => '081234567890',
    ]);

    $response = $this->postJson('/api/account-deletion', [
        'phone' => '+6281234567890',
    ]);

    $response->assertStatus(200);
    $this->assertSoftDeleted('users', [
        'id' => $user->id,
    ]);
});

test('account deletion works with 62 prefix', function () {
    $user = User::factory()->create([
        'phone' => '081234567890',
    ]);

    $response = $this->postJson('/api/account-deletion', [
        'phone' => '6281234567890',
    ]);

    $response->assertStatus(200);
    $this->assertSoftDeleted('users', [
        'id' => $user->id,
    ]);
});

test('account deletion requires phone field', function () {
    $response = $this->postJson('/api/account-deletion', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['phone']);
});

test('account deletion is rate limited', function () {
    $response = $this->postJson('/api/account-deletion', [
        'phone' => '081234567890',
    ]);

    $response->assertStatus(404); // User not found

    // Try 5 more times quickly (exceeds rate limit of 5 per minute)
    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/account-deletion', [
            'phone' => '081234567890',
        ]);
    }

    // Next request should be rate limited
    $response = $this->postJson('/api/account-deletion', [
        'phone' => '081234567890',
    ]);

    $response->assertStatus(429);
});
```

**Step 3: Run tests**

```bash
php artisan test --compact --filter=AccountDeletion
```

Expected: All tests PASS

**Step 4: Commit**

```bash
git add tests/Feature/AccountDeletionTest.php
git commit -m "test: add comprehensive account deletion feature tests"
```

---

## Task 6: Run Pint for Code Formatting

**Step 1: Run Laravel Pint**

```bash
vendor/bin/pint --dirty --format agent
```

**Step 2: Commit formatting changes if any**

```bash
git add -A
git commit -m "style: run pint on new files"
```

---

## Task 7: Verify Frontend Works

**Step 1: Visit the page**

Run (if not already running):
```bash
composer run dev
```

Open browser to: `http://laz-web.test/account-deletion`

**Step 2: Test the form manually**

1. Enter invalid phone format → Should show error
2. Enter valid phone (not in database) → Should show "not found" message
3. Enter valid phone (create test user first) → Should show success message

**Step 3: Create test user if needed**

```bash
php artisan tinker
```

```php
User::factory()->create(['phone' => '081234567890']);
```

**Step 4: Test deletion with created user**

Use the phone `081234567890` in the form → Should succeed

**Step 5: Verify soft delete in database**

```bash
php artisan tinker
```

```php
User::withTrashed()->where('phone', '081234567890')->first();
```

Should show user with `deleted_at` timestamp

**Step 6: Commit any fixes**

```bash
git add -A
git commit -m "fix: frontend issues discovered during testing"
```

---

## Task 8: Run All Tests

**Step 1: Run full test suite**

```bash
php artisan test --compact
```

Expected: All tests PASS (including existing tests)

**Step 2: If any tests fail**

Fix issues and re-run until all pass

**Step 3: Commit final fixes**

```bash
git add -A
git commit -m "fix: ensure all tests pass"
```

---

## Task 9: Documentation Update

**Step 1: Update README if needed**

If you want to document the new endpoint, add to `README.md`:

```markdown
## API Endpoints

### Account Deletion

**POST** `/api/account-deletion`

Delete user account by phone number.

Request body:
```json
{
  "phone": "08123456789"
}
```

Rate limited: 5 requests per minute
```

**Step 2: Commit documentation**

```bash
git add README.md
git commit -m "docs: add account deletion endpoint documentation"
```

---

## Completion Checklist

- [ ] API route added with rate limiting
- [ ] Controller method implemented with validation
- [ ] Web route added for frontend page
- [ ] Blade view created with form and JavaScript
- [ ] Feature tests written and passing
- [ ] Code formatted with Pint
- [ ] Frontend tested manually
- [ ] All tests passing
- [ ] Documentation updated

## Notes

- This feature uses the existing `softDeletes` trait in User model
- Phone validation accepts Indonesian formats: 08xxx, 628xxx, +628xxx
- Rate limiting matches existing auth endpoints (5 req/min)
- Frontend uses vanilla JavaScript with Fetch API
- No authentication required (public endpoint as required for Google Play)
