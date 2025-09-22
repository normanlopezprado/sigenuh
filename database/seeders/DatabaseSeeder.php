<?php

namespace Database\Seeders;

use App\Models\Nivel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\User;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::firstOrCreate(
            ['email' => 'root@gmail.com'], // evitar duplicados
            [
                'name'     => 'Norman Daniel López Prado',
                'user'     => 'nodalopr',
                'email'    => 'root@gmail.com',
                'password' => Hash::make('admin'),
            ]
        );
        $this->call([
            ServiceSeeder::class,
            HospitalSeeder::class,
            NivelesSeeder::class,
            RolePermissionSeeder::class
        ]);
    }
}
