<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_APPROVED
            ],
            [
                'name' => 'Staff User',
                'email' => 'staff@example.com',
                'role' => User::ROLE_STAFF,
                'status' => User::STATUS_APPROVED
            ],
            [
                'name' => 'Basic User',
                'email' => 'basic@example.com',
                'role' => User::ROLE_BASIC,
                'status' => User::STATUS_APPROVED
            ],
        ];

        foreach ($users as $user) {
            User::factory()->create($user);
        }

        User::factory(100)->create();
    }
}
