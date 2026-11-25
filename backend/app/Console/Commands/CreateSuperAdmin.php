<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class CreateSuperAdmin extends Command
{
    protected $signature = 'admin:create-super-admin';

    protected $description = 'Create the first super admin account';

    public function handle(): int
    {
        if (User::role('super-admin')->exists()) {
            $this->error('A super admin already exists. Manage additional accounts in the admin panel.');

            return self::FAILURE;
        }

        if (!Role::where('name', 'super-admin')->where('guard_name', 'web')->exists()) {
            $this->error('Run "php artisan migrate --seed" before creating the first super admin.');

            return self::FAILURE;
        }

        $attributes = [
            'name' => trim($this->ask('Name') ?? ''),
            'email' => trim($this->ask('Email') ?? ''),
            'password' => $this->secret('Password (minimum 12 characters with upper, lower, and numeric characters)') ?? '',
        ];
        $attributes['password_confirmation'] = $this->secret('Confirm password') ?? '';

        $validator = Validator::make($attributes, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(12)->letters()->mixedCase()->numbers()],
        ]);

        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            return self::FAILURE;
        }

        if (!$this->confirm('Create this super admin account?', true)) {
            $this->warn('No account was created.');

            return self::SUCCESS;
        }

        $user = User::create([
            'name' => $attributes['name'],
            'email' => $attributes['email'],
            'password' => Hash::make($attributes['password']),
            'is_active' => true,
            'must_change_password' => false,
        ]);
        $user->assignRole('super-admin');

        $this->info('Super admin created. Sign in at /admin/login.');

        return self::SUCCESS;
    }
}
