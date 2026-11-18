<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        $adminPassword = config('app.admin_initial_password');

        if (! is_string($adminPassword) || strlen($adminPassword) < 16) {
            throw new \RuntimeException(
                'Set ADMIN_INITIAL_PASSWORD to a unique password of at least 16 characters before seeding users.'
            );
        }

        $admin = User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make($adminPassword),
            ]
        );

        $admin->assignRole('admin');

        $petugas = User::firstOrCreate(
            ['email' => 'petugas@gmail.com'],
            [
                'name' => 'Petugas',
                'password' => Hash::make('password123'),
            ]
        );

        $petugas->assignRole('petugas');

        $anggota = User::firstOrCreate(
            ['email' => 'siswa@gmail.com'],
            [
                'name' => 'Anggota',
                'password' => Hash::make('password123'),
            ]
        );

        $studentRole = Role::query()
            ->whereIn('name', ['siswa', 'anggota'])
            ->value('name');

        if (! $studentRole) {
            throw new \RuntimeException(
                'Run RolePermissionSeeder before assigning the student role.'
            );
        }

        $anggota->syncRoles([$studentRole]);
    }
}
