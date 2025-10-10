<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ingredient;

class IngredientSeeder extends Seeder
{
    public function run(): void
    {
        $ingredients = [

            ['name' => 'Pollo',        'category' => 'Proteína', 'unit' => 'lb.', 'notes' => null],
            ['name' => 'Carne de res', 'category' => 'Proteína', 'unit' => 'lb.', 'notes' => null],
            ['name' => 'Huevo',        'category' => 'Proteína', 'unit' => 'ud.', 'notes' => null],
            ['name' => 'Pescado',      'category' => 'Proteína', 'unit' => 'lb.', 'notes' => null],
            ['name' => 'Jamón',        'category' => 'Proteína', 'unit' => 'lb.', 'notes' => null],

            
            ['name' => 'Leche',        'category' => 'Lácteos',  'unit' => 'L.',  'notes' => null],
            ['name' => 'Queso',        'category' => 'Lácteos',  'unit' => 'lb.', 'notes' => null],
            ['name' => 'Yogurt',       'category' => 'Lácteos',  'unit' => 'L.',  'notes' => null],
            ['name' => 'Mantequilla',  'category' => 'Lácteos',  'unit' => 'lb.', 'notes' => null],

            
            ['name' => 'Manzana',      'category' => 'Fruta',    'unit' => 'lb.', 'notes' => null],
            ['name' => 'Banano',       'category' => 'Fruta',    'unit' => 'lb.', 'notes' => null],
            ['name' => 'Papaya',       'category' => 'Fruta',    'unit' => 'lb.', 'notes' => null],
            ['name' => 'Naranja',      'category' => 'Fruta',    'unit' => 'lb.', 'notes' => null],

            
            ['name' => 'Tomate',       'category' => 'Verdura',  'unit' => 'lb.', 'notes' => null],
            ['name' => 'Lechuga',      'category' => 'Verdura',  'unit' => 'lb.', 'notes' => null],
            ['name' => 'Zanahoria',    'category' => 'Verdura',  'unit' => 'lb.', 'notes' => null],
            ['name' => 'Cebolla',      'category' => 'Verdura',  'unit' => 'lb.', 'notes' => null],
            ['name' => 'Papa',         'category' => 'Verdura',  'unit' => 'lb.', 'notes' => null],
            ['name' => 'Brócoli',      'category' => 'Verdura',  'unit' => 'lb.', 'notes' => null],

        
            ['name' => 'Sal',          'category' => 'Condimento', 'unit' => 'lb.', 'notes' => null],
            ['name' => 'Azúcar',       'category' => 'Condimento', 'unit' => 'lb.', 'notes' => null],
            ['name' => 'Aceite vegetal','category' => 'Condimento', 'unit' => 'L.',  'notes' => null],
            ['name' => 'Pimienta',     'category' => 'Condimento', 'unit' => 'g.',  'notes' => null],
            ['name' => 'Ajo',          'category' => 'Condimento', 'unit' => 'lb.', 'notes' => null],
        ];

        foreach ($ingredients as $data) {
            Ingredient::firstOrCreate(['name' => $data['name']], $data);
        }
    }
}
