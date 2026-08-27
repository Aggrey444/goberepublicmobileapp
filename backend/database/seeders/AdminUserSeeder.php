<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@gobe.com')],
            [
                'name' => 'Super Admin',
                'phone' => null,
                'password' => env('ADMIN_PASSWORD', 'password'),
                'role' => User::ROLE_SUPER_ADMIN,
                'status' => User::STATUS_ACTIVE,
            ]
        );
    }
}
