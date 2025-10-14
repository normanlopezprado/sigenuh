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
        $this->call([
            RolePermissionSeeder::class,
            ServiceSeeder::class,
            HospitalSeeder::class,
            NivelesSeeder::class,
            IngredientSeeder::class,
            StaffBeneficiarySeeder::class,
            CartSeeder::class,

        ]);

        $user = User::firstOrCreate(
            ['email' => 'nlopezp1@miumg.edu.gt'],
            [
                'name'              => 'Norman Daniel López Prado',
                'user'              => 'nodalopr',
                'password'          => Hash::make('admin'),
                'email_verified_at' => now(),
            ]
        );

        $user->syncRoles(['Administrador']);
    }
}
