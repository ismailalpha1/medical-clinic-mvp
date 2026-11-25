<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::with('roles')->whereHas('roles')->orderBy('name')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.form', ['adminUser' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(['admin', 'super-admin'])],
        ]);
        $temporaryPassword = bin2hex(random_bytes(24));
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $temporaryPassword,
            'is_active' => true,
            'must_change_password' => true,
        ]);
        $user->assignRole($validated['role']);

        return redirect()->route('admin.users.index')
            ->with('temporary_credentials', [
                'email' => $user->email,
                'password' => $temporaryPassword,
            ])
            ->with('status', __('admin.user_created'));
    }

    public function edit(User $user): View
    {
        abort_unless($user->hasAnyRole(['admin', 'super-admin']), 404);

        return view('admin.users.form', ['adminUser' => $user]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->hasAnyRole(['admin', 'super-admin']), 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(['admin', 'super-admin'])],
            'is_active' => ['required', 'boolean'],
        ]);

        if ($user->is(auth()->user())
            && ($validated['role'] !== 'super-admin' || !$validated['is_active'])) {
            return back()->withErrors(['role' => __('admin.cannot_change_own_access')])->withInput();
        }

        $removingActiveSuperAdmin = $user->hasRole('super-admin')
            && ($validated['role'] !== 'super-admin' || !$validated['is_active'])
            && $user->is_active;
        if ($removingActiveSuperAdmin
            && User::role('super-admin')->where('is_active', true)->count() <= 1) {
            return back()->withErrors(['role' => __('admin.last_super_admin')])->withInput();
        }

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'is_active' => $validated['is_active'],
        ]);
        $user->syncRoles($validated['role']);

        return redirect()->route('admin.users.index')
            ->with('status', __('admin.user_updated'));
    }

    public function resetPassword(User $user): RedirectResponse
    {
        abort_unless($user->hasAnyRole(['admin', 'super-admin']), 404);

        if ($user->is(auth()->user())) {
            return back()->withErrors(['user' => __('admin.cannot_reset_own_password')]);
        }

        $temporaryPassword = bin2hex(random_bytes(24));
        $user->update([
            'password' => $temporaryPassword,
            'must_change_password' => true,
        ]);

        return redirect()->route('admin.users.index')
            ->with('temporary_credentials', [
                'email' => $user->email,
                'password' => $temporaryPassword,
            ])
            ->with('status', __('admin.password_reset'));
    }
}
