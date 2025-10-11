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
            //beneficiarios
            'staff-beneficiaries.index',
            'staff-beneficiaries.create',
            'staff-beneficiaries.edit',
            'staff-beneficiaries.delete',
            'staff-meals.view',      
            'staff-meals.deliver',   
            'staff-meals.report',   
            // Recolección (collects)
            'collects.index',         
            'collects.bulk',          
            'collects.toggle-bed',    
            'collects.save-companion',

            //carritos
            'carts.index',
            'carts.create',
            'carts.edit',
            'carts.delete',
            'carts.routes.edit',
            'carts.routes.update',
            'carts.services.view',

            //calendarios
            'calendars.index',
            'calendars.create',
            'calendars.edit',
            'calendars.delete',


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

             // beneficiarios
            'staff-beneficiaries.index',
            'staff-beneficiaries.create',
            'staff-beneficiaries.edit',
            'staff-meals.view',
            'staff-meals.deliver',
            'staff-meals.report',

            //collects
            'collects.index',
            'collects.bulk',
            'collects.toggle-bed',
            'collects.save-companion',

            //carritos
            'carts.index',
            'carts.create',
            'carts.edit',
            'carts.delete',
            'carts.routes.edit',
            'carts.routes.update',
            'carts.services.view',

            //calendarios
            'calendars.index',
            'calendars.create',
            'calendars.edit',
            'calendars.delete',

        ]);

        $recolector->syncPermissions([
            'dashboard.view',

             //beneficiarios
            'staff-meals.view',
            'staff-meals.deliver',
            'staff-meals.report',

            //collects
            'collects.index',
            'collects.bulk',
            'collects.toggle-bed',
            'collects.save-companion',

        ]);

        $visualizador->syncPermissions([
            'dashboard.view',

            //beneficiarios
            'staff-meals.view',
            'staff-meals.deliver',
            'staff-meals.report',

        ]);
    }
}
