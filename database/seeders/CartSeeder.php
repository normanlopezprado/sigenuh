<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Cart;
use App\Models\Hospital;

class CartSeeder extends Seeder
{
    public function run(): void
    {

        $hospitalId = Hospital::first()?->id;

        if (!$hospitalId) {
            $this->command->warn("No hay hospitales en la base de datos. No se crearon carritos.");
            return;
        }

        $carts = [
            ['name' => 'Carrito #1', 'code_name' => 'Servicio', 'color' => '#007bff'],   
            ['name' => 'Carrito #2', 'code_name' => 'Café',     'color' => '#795548'],   
            ['name' => 'Carrito #3', 'code_name' => 'Dieta',    'color' => '#28a745'],   
            ['name' => 'Carrito #4', 'code_name' => 'Líquidos', 'color' => '#17a2b8'],   
        ];

        foreach ($carts as $index => $cart) {
            Cart::firstOrCreate(
                [
                    'hospital_id' => $hospitalId,
                    'name' => $cart['name']
                ],
                [
                    'id' => (string) Str::uuid(),
                    'code_name' => $cart['code_name'],
                    'color' => $cart['color'],
                    'order' => $index + 1,
                    'status' => true,
                    'notes' => null,
                ]
            );
        }

        $this->command->info("Carritos creados para el hospital {$hospitalId}");
    }
}
