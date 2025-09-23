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
            //hospitales
            'hospitales.index',
            'hospitales.create',
            'hospitales.edit',
            'hospitales.delete',
            //niveles del hospital
            'niveles.index',
            'niveles.create',
            'niveles.edit',
            'niveles.delete',
            //plantas de hospital
            'hospitalfloors.edit',
            'hospitalfloors.update',
            //servicios
            'servicios.index',
            'servicios.create',
            'servicios.edit',
            'servicios.delete',
            'servicios.show',
            //servicios por piso
            'hospital-floor-services.edit',
            'hospital-floor-services.update',
            //camas
            'beds.index',
            'beds.create',
            'beds.edit',
            'beds.delete',
            //usuarios
            'users.index',
            'users.create',
            'users.edit',
            'users.delete',
            //ingredientes
            'ingredients.index',
            'ingredients.create',
            'ingredients.edit',
            'ingredients.delete',
            //menus
            'menus.index',
            'menus.create',
            'menus.edit',
            'menus.delete',

            //dashboards
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
            //dashboard
            'dashboard.view',
            //ingredientes
            'ingredients.index',
            'ingredients.create',
            'ingredients.edit',
            //menu
            'menus.index',
            'menus.create',
            'menus.edit',
        ]);

        $recolector->syncPermissions([
            'dashboard.view',


        ]);

        $visualizador->syncPermissions([
            'dashboard.view',


        ]);
    }
}
