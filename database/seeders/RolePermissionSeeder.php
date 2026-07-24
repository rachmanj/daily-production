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
            'master.manage',
            'equipment.assign',
            'entry.create',
            'entry.approve',
            'dashboard.view',
            'plan.manage',
            'report.generate',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $admin = Role::firstOrCreate(['name' => UserRole::Admin->value]);
        $admin->syncPermissions(Permission::all());

        $supervisor = Role::firstOrCreate(['name' => UserRole::Supervisor->value]);
        $supervisor->syncPermissions([
            'entry.create',
            'entry.approve',
            'dashboard.view',
            'report.generate',
        ]);

        $management = Role::firstOrCreate(['name' => UserRole::Management->value]);
        $management->syncPermissions([
            'dashboard.view',
            'plan.manage',
            'report.generate',
        ]);

        $fuelOfficer = Role::firstOrCreate(['name' => UserRole::FuelOfficer->value]);
        $fuelOfficer->syncPermissions([
            'entry.create',
            'dashboard.view',
        ]);
    }
}
