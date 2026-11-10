<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleAndPermissionSeeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            DepartmentSeeder::class,
        ]);

        // Create default admin user
        // michaelbalivia@cpsu.edu.ph

        $admin = User::create([
            'first_name' => 'System',
            'last_name' => 'Administrator',
            'email' => 'admin@cpsu.edu.ph',
            'password' => bcrypt('Admin@123!'),
            'department_id' => 1,
            'position' => 'System Administrator',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('administrator');

        // Create records officer
        $officer = User::create([
            'first_name' => 'Records',
            'last_name' => 'Officer',
            'email' => 'officer@cpsu.edu.ph',
            'password' => bcrypt('Officer@123!'),
            'department_id' => 2,
            'position' => 'Records Officer',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $officer->assignRole('records_officer');

        // Create sample staff
        $staff = User::create([
            'first_name' => 'Records',
            'last_name' => 'Staff',
            'email' => 'staff@cpsu.edu.ph',
            'password' => bcrypt('Staff@123!'),
            'department_id' => 2,
            'position' => 'Records Staff',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $staff->assignRole('records_staff');
    }
}