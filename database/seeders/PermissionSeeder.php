<?php

namespace Database\Seeders;

use App\Enums\PermissionType;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (PermissionType::values() as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
