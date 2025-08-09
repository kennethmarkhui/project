<?php

namespace Database\Seeders;

use App\Enums\RoleType;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (RoleType::cases() as $role) {
            $roleModel = Role::firstOrCreate(['name' => $role->value]);

            if (!empty($role->permissions())) {
                $permissions = array_map(
                    fn($permission) => $permission->value,
                    $role->permissions()
                );
                $roleModel->syncPermissions($permissions);
            }
        }
    }
}
