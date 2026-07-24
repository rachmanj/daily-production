<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view dashboard',
            'create daily-entry',
            'view production',
            'view fuel',
            'create fuel-record',
            'manage fuel-receipt',
            'view equipment',
            'view reports',
            'view plan',
            'manage sites',
            'manage users',
            'manage roles',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $admin = Role::firstOrCreate(['name' => UserRole::Admin->value]);
        $admin->syncPermissions(Permission::all());

        $supervisor = Role::firstOrCreate(['name' => UserRole::Supervisor->value]);
        $supervisor->syncPermissions([
            'create daily-entry',
            'view production',
            'view fuel',
            'view equipment',
        ]);

        $management = Role::firstOrCreate(['name' => UserRole::Management->value]);
        $management->syncPermissions([
            'view dashboard',
            'view reports',
            'view plan',
        ]);

        $fuelOfficer = Role::firstOrCreate(['name' => UserRole::FuelOfficer->value]);
        $fuelOfficer->syncPermissions([
            'create fuel-record',
            'view fuel',
            'manage fuel-receipt',
        ]);
    }
}
