<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'hospitales.index',
            'hospitales.create',
            'hospitales.edit',

            'niveles.index',
            'niveles.create',
            'niveles.edit',

            'hospital-floors.edit',

            'servicios.index',
            'servicios.create',
            'servicios.edit',

            'hospital-floor-services.edit',

            'beds.index',
            'beds.create',
            'beds.edit',

            'users.index',
            'users.create',
            'users.edit',




            'dashboard',
            'dashboard.view',




        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $admin        = Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);
        $nutricion    = Role::firstOrCreate(['name' => 'Nutrición',     'guard_name' => 'web']);
        $recolector   = Role::firstOrCreate(['name' => 'Recolector',    'guard_name' => 'web']);
        $visualizador = Role::firstOrCreate(['name' => 'Visualizador',  'guard_name' => 'web']);

        // Asignación de permisos
        $admin->syncPermissions(Permission::pluck('name')->all());

        $nutricion->syncPermissions([
            'dashboard',
            'dashboard.view',

        ]);

        $recolector->syncPermissions([
            'dashboard',
            'dashboard.view',


        ]);

        $visualizador->syncPermissions([
            'dashboard',
            'dashboard.view',


        ]);
    }
}
