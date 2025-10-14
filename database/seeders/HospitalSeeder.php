<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Hospital;
use Illuminate\Support\Str;

class HospitalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hospitals = [
            [
                'id'          => (string) Str::uuid(),
                'name'        => 'Hospital Regional de Occidente',
                'address'     => '4a. Calle 19-50, Zona 3, Quetzaltenango, Guatemala',
                'email'       => 'contacto@hro.gob.gt',
                'phone'       => '7765-4321',
                'description' => 'Hospital público de referencia en la región occidental de Guatemala.',
                'logo_path'   => null,
                'icon_path'   => null,
                'latitude'    => 14.859277,
                'longitude'   => -91.539937,
            ],
        ];

        foreach ($hospitals as $h) {
            Hospital::firstOrCreate(['name' => $h['name']], $h);
        }
    }
}
