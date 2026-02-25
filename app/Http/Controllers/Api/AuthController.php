<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $normalizedPhone = $this->normalizePhoneNumber((string) $request->input('phone', ''));

        $request->merge([
            'phone' => $normalizedPhone,
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users,email',
            'phone' => ['required', 'string', 'regex:/^08[1-9][0-9]{6,11}$/', 'unique:users,phone'],
            'password' => 'required|string|min:8|confirmed',
        ], [
            'phone.regex' => 'Format nomor telepon harus dimulai dengan 08, 628, atau +628',
        ]);

        $resolvedEmail = $this->resolveRegistrationEmail(
            (string) ($validated['email'] ?? ''),
            $validated['phone']
        );

        $user = User::create([
            'name' => $validated['name'],
            'email' => $resolvedEmail,
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    public function login(Request $request)
    {
        $request->merge([
            'phone' => $this->normalizePhoneNumber((string) $request->input('phone', '')),
        ]);

        $validated = $request->validate([
            'phone' => ['required', 'string', 'regex:/^08[1-9][0-9]{6,11}$/'],
            'password' => ['required', 'string'],
        ], [
            'phone.regex' => 'Format nomor telepon harus dimulai dengan 08, 628, atau +628',
        ]);

        $user = User::query()
            ->whereIn('phone', $this->phoneVariants($validated['phone']))
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid login details',
            ], 401);
        }

        if ($user->phone !== $validated['phone']) {
            $user->forceFill([
                'phone' => $validated['phone'],
            ])->save();
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function user(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar_url' => MediaUrl::resolve($request, $user->avatar_url),
            'email_verified_at' => $user->email_verified_at,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    public function deleteAccount(Request $request)
    {
        $request->merge([
            'phone' => $this->normalizePhoneNumber((string) $request->input('phone', '')),
        ]);

        $validated = $request->validate([
            'phone' => ['required', 'string', 'regex:/^08[1-9][0-9]{6,11}$/'],
        ], [
            'phone.regex' => 'Format nomor telepon harus dimulai dengan 08, 628, atau +628',
        ]);

        $user = User::query()
            ->whereIn('phone', $this->phoneVariants($validated['phone']))
            ->first();

        if (! $user) {
            return response()->json([
                'message' => 'Akun dengan nomor telepon tersebut tidak ditemukan',
            ], 404);
        }

        if ($user->phone !== $validated['phone']) {
            $user->forceFill([
                'phone' => $validated['phone'],
            ])->save();
        }

        $user->delete();

        return response()->json([
            'message' => 'Akun berhasil dihapus',
        ]);
    }

    private function normalizePhoneNumber(string $phone): string
    {
        $normalizedPhone = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($normalizedPhone, '62')) {
            return '0' . substr($normalizedPhone, 2);
        }

        if (str_starts_with($normalizedPhone, '8')) {
            return '0' . $normalizedPhone;
        }

        return $normalizedPhone;
    }

    /**
     * @return array<int, string>
     */
    private function phoneVariants(string $normalizedPhone): array
    {
        $localPhone = ltrim($normalizedPhone, '0');

        return array_values(array_filter(array_unique([
            $normalizedPhone,
            $localPhone !== '' ? '62' . $localPhone : null,
            $localPhone !== '' ? '+62' . $localPhone : null,
        ])));
    }

    private function resolveRegistrationEmail(string $email, string $normalizedPhone): string
    {
        $trimmedEmail = strtolower(trim($email));

        if ($trimmedEmail !== '') {
            return $trimmedEmail;
        }

        return $normalizedPhone . '@phone.lazalazhar5.local';
    }
}
