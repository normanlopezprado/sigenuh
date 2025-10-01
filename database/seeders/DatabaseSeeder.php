<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1) Primero: roles y permisos (para poder asignarlos al usuario root)
        $this->call([
            RolePermissionSeeder::class,
            ServiceSeeder::class,
            HospitalSeeder::class,
            NivelesSeeder::class,
            IngredientSeeder::class,
            StaffBeneficiarySeeder::class,

        ]);

        // 2) Usuario root (idempotente)
        $user = User::firstOrCreate(
            ['email' => 'nlopezp1@miumg.edu.gt'], // evita duplicados por email
            [
                'name'              => 'Norman Daniel López Prado',
                'user'              => 'nodalopr',
                'password'          => Hash::make('admin'),
                'email_verified_at' => now(),
            ]
        );

        // 3) Asignar rol Administrador (idempotente)
        // Requiere que tu modelo User use el trait: use Spatie\Permission\Traits\HasRoles;
        $user->syncRoles(['Administrador']);
    }
}
