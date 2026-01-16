<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DevUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Local/dev convenience accounts for quickly testing role-based UI.
        // Login uses the `password_hash` column (see User::getAuthPassword()).
        $users = [
            [
                'email' => 'admin@example.com',
                'role' => 'admin',
                'first_name' => 'Admin',
                'last_name' => 'User',
                'contact' => '5550000001',
            ],
            [
                'email' => 'it@example.com',
                'role' => 'it',
                'first_name' => 'IT',
                'last_name' => 'Support',
                'contact' => '5550000002',
            ],
            [
                'email' => 'user@example.com',
                'role' => 'user',
                'first_name' => 'Normal',
                'last_name' => 'User',
                'contact' => '5550000003',
            ],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'contact' => $data['contact'],
                    'password_hash' => Hash::make('password'),
                    'role' => $data['role'],
                    'is_active' => true,
                ]
            );
        }
    }
}
