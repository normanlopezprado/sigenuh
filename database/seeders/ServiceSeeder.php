<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            ['name' => 'Cirugía',          'abbreviation' => 'CH',   'category' => 'Hombres'],
            ['name' => 'Cirugía',          'abbreviation' => 'CM',   'category' => 'Mujeres'],
            ['name' => 'Especialidad',     'abbreviation' => 'EH',   'category' => 'Hombres'],
            ['name' => 'Especialidad',     'abbreviation' => 'EM',   'category' => 'Mujeres'],
            ['name' => 'Ginecología',      'abbreviation' => 'Gine', 'category' => 'Mujeres'],
            ['name' => 'Medicina Interna', 'abbreviation' => 'MIM',  'category' => 'Mujeres'],
            ['name' => 'Medicina Interna', 'abbreviation' => 'MIH',  'category' => 'Hombres'],
            ['name' => 'Post Parto',       'abbreviation' => 'PP',   'category' => 'Mujeres'],
            ['name' => 'Pre Escolares',    'abbreviation' => 'PE',   'category' => 'Menores'],
            ['name' => 'Sala Cuna',        'abbreviation' => 'SC',   'category' => 'Menores'],
            ['name' => 'Traumatología',    'abbreviation' => 'TH',   'category' => 'Hombres'],
            ['name' => 'Traumatología',    'abbreviation' => 'TM',   'category' => 'Mujeres'],
        ];

        foreach ($services as $r) {
            \App\Models\Service::firstOrCreate(
                [
                    'name'        => $r['name'],
                    'abbreviation'=> $r['abbreviation'],
                    'category'    => $r['category'],
                ],
                $r
            );
        }
    }
}
