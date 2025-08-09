<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class FeatureTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class
        ]);

        // update cache to know about the newly created permissions (required if using WithoutModelEvents in seeders)
        // app()[PermissionRegistrar::class]->forgetCachedPermissions();

    }
}
