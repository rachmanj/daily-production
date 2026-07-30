<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Supervisor 022C',
                'username' => 'sup022c',
                'email' => 'sup022c@mineops.test',
                'password' => bcrypt('password'),
                'role' => 'supervisor',
            ],
            [
                'name' => 'Supervisor 021C',
                'username' => 'sup021c',
                'email' => 'sup021c@mineops.test',
                'password' => bcrypt('password'),
                'role' => 'supervisor',
            ],
            [
                'name' => 'Manajemen',
                'username' => 'manager',
                'email' => 'manager@mineops.test',
                'password' => bcrypt('password'),
                'role' => 'management',
            ],
            [
                'name' => 'Petugas BBM',
                'username' => 'fuel',
                'email' => 'fuel@mineops.test',
                'password' => bcrypt('password'),
                'role' => 'fuel_officer',
            ],
            [
                'name' => 'Operator CCR',
                'username' => 'ccr',
                'email' => 'ccr@mineops.test',
                'password' => bcrypt('password'),
                'role' => 'supervisor',
            ],
            [
                'name' => 'Admin Test',
                'username' => 'admin2',
                'email' => 'admin2@mineops.test',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ],
        ];

        foreach ($users as $data) {
            $role = $data['role'];
            unset($data['role']);

            $data['is_active'] = true;

            $user = User::firstOrCreate(
                ['username' => $data['username']],
                $data,
            );

            $user->syncRoles([$role]);
        }
    }
}
