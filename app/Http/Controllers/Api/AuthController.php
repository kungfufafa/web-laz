<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\MediaUrl;
use App\Support\PhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $normalizedPhone = PhoneNumber::normalize((string) $request->input('phone', ''));

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

        $resolvedEmail = PhoneNumber::resolveEmail(
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
            'phone' => PhoneNumber::normalize((string) $request->input('phone', '')),
        ]);

        $validated = $request->validate([
            'phone' => ['required', 'string', 'regex:/^08[1-9][0-9]{6,11}$/'],
            'password' => ['required', 'string'],
        ], [
            'phone.regex' => 'Format nomor telepon harus dimulai dengan 08, 628, atau +628',
        ]);

        $user = User::query()
            ->whereIn('phone', PhoneNumber::variants($validated['phone']))
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
            'phone' => PhoneNumber::normalize((string) $request->input('phone', '')),
        ]);

        $validated = $request->validate([
            'phone' => ['required', 'string', 'regex:/^08[1-9][0-9]{6,11}$/'],
        ], [
            'phone.regex' => 'Format nomor telepon harus dimulai dengan 08, 628, atau +628',
        ]);

        $user = User::query()
            ->whereIn('phone', PhoneNumber::variants($validated['phone']))
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
}
