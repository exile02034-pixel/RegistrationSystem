<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'], // prevent duplicates
            [
                'name' => 'System Administrator',
                'password' => Hash::make('admin123'),
                'role' => 'admin', // make sure you have this column
                'email_verified_at' => now(),
            ]
        );
    }
}
