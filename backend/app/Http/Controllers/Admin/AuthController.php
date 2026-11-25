<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View
    {
        return view('admin.auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'is_active' => true,
        ], $request->boolean('remember'))) {
            return back()->withErrors(['email' => __('admin.login_failed')])
                ->onlyInput('email');
        }

        $request->session()->regenerate();
        $user = $request->user();

        if (!$user->can('admin.access')) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors(['email' => __('admin.login_failed')])
                ->onlyInput('email');
        }

        return redirect()->intended(
            $user->must_change_password
                ? route('admin.password.edit')
                : route('admin.dashboard'),
        );
    }

    public function updateLocale(Request $request): RedirectResponse
    {
        $validated = $request->validate(['locale' => ['required', 'in:en,es']]);
        $request->session()->put('admin_locale', $validated['locale']);

        return back();
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    public function editPassword(): View
    {
        return view('admin.auth.password');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(12)->letters()->mixedCase()->numbers()],
        ]);

        $user = $request->user();
        $user->password = Hash::make($validated['password']);
        $user->must_change_password = false;
        $user->save();
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard')
            ->with('status', __('admin.password_updated'));
    }
}
