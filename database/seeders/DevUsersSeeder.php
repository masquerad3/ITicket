<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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
            $rows = DB::select('EXEC dbo.sp_read_user_by_email @email = ?', [$data['email']]);
            $existing = $rows[0] ?? null;

            if (!$existing) {
                DB::select(
                    'EXEC dbo.sp_create_user @first_name=?, @last_name=?, @email=?, @contact=?, @password_hash=?, @role=?',
                    [
                        $data['first_name'],
                        $data['last_name'],
                        $data['email'],
                        $data['contact'],
                        Hash::make('password'),
                        $data['role'],
                    ]
                );
                continue;
            }

            DB::select(
                'EXEC dbo.sp_update_user @user_id=?, @first_name=?, @last_name=?, @email=?, @contact=?, @role=?, @is_active=?',
                [
                    (int) $existing->user_id,
                    $data['first_name'],
                    $data['last_name'],
                    $data['email'],
                    $data['contact'],
                    $data['role'],
                    1,
                ]
            );
        }
    }
}
