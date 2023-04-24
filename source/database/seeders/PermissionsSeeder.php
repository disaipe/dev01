<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        Permission::create(['name' => 'super admin']);
        Permission::create(['name' => 'admin']);

        // create roles and assign existing permissions
        /** @var Role $superAdmin */
        $superAdmin = Role::create(['name' => 'super admin']);
        $superAdmin->givePermissionTo('super admin');

        /** @var Role $admin */
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo('admin');
    }
}
