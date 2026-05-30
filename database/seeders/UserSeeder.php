<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['mobile' => '09120000000'],
            [
                'name' => 'مدیر سیستم (Super Admin)',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
            ]
        );

        // Assign the Super Admin role (created in RolesAndPermissionsSeeder)
        if (!$admin->hasRole('Super Admin')) {
            $admin->assignRole('Super Admin');
        }

        // We can also create a default designer for testing
        $designer = User::firstOrCreate(
            ['mobile' => '09121111111'],
            [
                'name' => 'طراح تستی ۱',
                'email' => 'designer@example.com',
                'password' => Hash::make('password'),
            ]
        );

        if (!$designer->hasRole('Question Designer')) {
            $designer->assignRole('Question Designer');
        }
    }
}
