<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'manage-comics',
            'access-admin-dashboard',
            'preview-comics',
            'upload-comics',
            'access-author-dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions([
            'manage-comics',
            'access-admin-dashboard',
            'preview-comics',
        ]);

        $penulisRole = Role::firstOrCreate(['name' => 'penulis']);
        $penulisRole->syncPermissions([
            'upload-comics',
            'access-author-dashboard',
        ]);

        $adminUser = User::firstOrCreate(
            ['email' => 'admin@webtoon.com'],
            ['name' => 'Super Admin', 'password' => bcrypt('password')]
        );
        $adminUser->assignRole('admin');
    }
}
