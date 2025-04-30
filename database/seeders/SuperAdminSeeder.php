<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run()
    {
        // Cek kalau role superadmin belum ada, buat dulu
        $role = Role::firstOrCreate(
            ['name' => 'superadmin', 'guard_name' => 'web']
        );

        // Buat user superadmin
        $user = User::firstOrCreate(
            ['email' => 'admin@smktelkom-lpg.sch.id'],
            [
                'name' => 'Super Admin',
                'email_verified_at' => now(),
                'password' => Hash::make('password'), // Default password "password", bisa diganti
            ]
        );

        // Assign role ke user
        if (!$user->hasRole('superadmin')) {
            $user->assignRole($role);
        }
    }
}
