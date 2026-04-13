<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\LoginWebUserRequest;
use App\Http\Requests\Web\RegisterWebUserRequest;
use App\Models\User;
use App\Support\PhoneNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginWebUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::query()
            ->whereIn('phone', PhoneNumber::variants($validated['phone']))
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return back()
                ->withErrors([
                    'phone' => 'Nomor telepon atau kata sandi tidak valid.',
                ])
                ->onlyInput('phone');
        }

        if ($user->phone !== $validated['phone']) {
            $user->forceFill([
                'phone' => $validated['phone'],
            ])->save();
        }

        Auth::login($user, true);
        $request->session()->regenerate();
        $this->clearGuestCheckoutSession($request);

        return redirect()->intended(route('ppob.index'));
    }

    public function createRegistration(): View
    {
        return view('auth.register');
    }

    public function register(RegisterWebUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => PhoneNumber::resolveEmail((string) ($validated['email'] ?? ''), $validated['phone']),
            'phone' => $validated['phone'],
            'password' => $validated['password'],
        ]);

        Auth::login($user, true);
        $request->session()->regenerate();
        $this->clearGuestCheckoutSession($request);

        return redirect()->route('ppob.index');
    }

    public function destroy(): RedirectResponse
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function clearGuestCheckoutSession(LoginWebUserRequest|RegisterWebUserRequest $request): void
    {
        $request->session()->forget([
            'ppob_guest_user_id',
            'ppob_guest_token',
            'ppob_guest_transactions',
        ]);
    }
}
