<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the admin user for the dashboard.
     * Since there's no registration (admin-only platform),
     * users are created via this seeder or artisan tinker.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@ai-ops.jaor13.app'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
    }
}
