<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SampleUserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Alice Johnson', 'email' => 'alice.user@example.com'],
            ['name' => 'Brian Smith', 'email' => 'brian.user@example.com'],
            ['name' => 'Carla Reyes', 'email' => 'carla.user@example.com'],
            ['name' => 'Daniel Cruz', 'email' => 'daniel.user@example.com'],
            ['name' => 'Ella Martin', 'email' => 'ella.user@example.com'],
            ['name' => 'Frank Lee', 'email' => 'frank.user@example.com'],
            ['name' => 'Grace Kim', 'email' => 'grace.user@example.com'],
            ['name' => 'Henry Walker', 'email' => 'henry.user@example.com'],
            ['name' => 'Ivy Scott', 'email' => 'ivy.user@example.com'],
            ['name' => 'Jacob Young', 'email' => 'jacob.user@example.com'],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => Hash::make('user12345'),
                    'role' => 'user',
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
