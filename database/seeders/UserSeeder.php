<?php

namespace Database\Seeders;

use App\Enums\RoleType;
use App\Enums\UserStatusType;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = array_map(
            fn($role) =>
            [
                'name' => $role,
                'email' => preg_replace('/\s+/', '', $role) . '@example.com',
                'status' => UserStatusType::APPROVED->value
            ],
            RoleType::values()
        );

        foreach ($users as $user) {
            $userModel = User::factory()->create($user);
            $userModel->syncRoles($user['name']);
        }

        User::factory(100)->create();
    }
}
