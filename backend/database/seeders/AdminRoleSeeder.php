<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AdminRoleSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissionNames = [
            'admin.access',
            'clinic-submissions.view',
            'clinic-submissions.create',
            'clinic-submissions.update',
            'clinic-submissions.delete',
            'contact-inquiries.view',
            'contact-inquiries.create',
            'contact-inquiries.update',
            'contact-inquiries.delete',
            'admin-users.manage',
        ];

        $permissions = collect($permissionNames)->map(
            fn (string $name) => Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']),
        );

        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $superAdmin->syncPermissions($permissions);
        $admin->syncPermissions($permissions->reject(
            fn (Permission $permission) => str_ends_with($permission->name, '.delete')
                || $permission->name === 'admin-users.manage',
        ));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
