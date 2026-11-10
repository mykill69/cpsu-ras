<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Folder permissions
            'view folders',
            'create folders',
            'edit folders',
            'delete folders',
            'archive folders',
            
            // File permissions
            'view files',
            'upload files',
            'download files',
            'delete files',
            'edit file metadata',
            
            // User management permissions
            'view users',
            'create users',
            'edit users',
            'delete users',
            'manage roles',
            
            // System permissions
            'view dashboard',
            'view activity logs',
            'manage settings',
            'view reports',
            'export data',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        // Create roles and assign permissions using firstOrCreate
        $adminRole = Role::firstOrCreate([
            'name' => 'administrator',
            'guard_name' => 'web'
        ]);
        $adminRole->givePermissionTo(Permission::all());

        $officerRole = Role::firstOrCreate([
            'name' => 'records_officer',
            'guard_name' => 'web'
        ]);
        $officerRole->givePermissionTo([
            'view folders', 'create folders', 'edit folders', 'delete folders', 'archive folders',
            'view files', 'upload files', 'download files', 'delete files', 'edit file metadata',
            'view users', 'view dashboard', 'view activity logs', 'view reports',
        ]);

        $staffRole = Role::firstOrCreate([
            'name' => 'records_staff',
            'guard_name' => 'web'
        ]);
        $staffRole->givePermissionTo([
            'view folders', 'create folders',
            'view files', 'upload files', 'download files',
            'view dashboard',
        ]);
    }
}