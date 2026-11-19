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
        $initialPasswords = [
            'ADMIN_INITIAL_PASSWORD' => config('app.admin_initial_password'),
            'PETUGAS_INITIAL_PASSWORD' => config('app.petugas_initial_password'),
            'SISWA_INITIAL_PASSWORD' => config('app.siswa_initial_password'),
        ];

        foreach ($initialPasswords as $environmentVariable => $password) {
            if (! is_string($password) || strlen($password) < 16) {
                throw new \RuntimeException(
                    "Set {$environmentVariable} to a unique password of at least 16 characters before seeding users."
                );
            }
        }

        if (count(array_unique($initialPasswords)) !== count($initialPasswords)) {
            throw new \RuntimeException(
                'Each initial user password must be unique.'
            );
        }

        $admin = User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make($initialPasswords['ADMIN_INITIAL_PASSWORD']),
            ]
        );

        $admin->assignRole('admin');

        $petugas = User::firstOrCreate(
            ['email' => 'petugas@gmail.com'],
            [
                'name' => 'Petugas',
                'password' => Hash::make($initialPasswords['PETUGAS_INITIAL_PASSWORD']),
            ]
        );

        $petugas->assignRole('petugas');

        $anggota = User::firstOrCreate(
            ['email' => 'siswa@gmail.com'],
            [
                'name' => 'Anggota',
                'password' => Hash::make($initialPasswords['SISWA_INITIAL_PASSWORD']),
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
