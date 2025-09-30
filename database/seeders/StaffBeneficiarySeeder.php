<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StaffBeneficiary;
use Illuminate\Support\Str;

class StaffBeneficiarySeeder extends Seeder
{
    public function run(): void
    {
        $beneficiaries = [
            ['full_name' => 'Juan Carlos López',      'job_title' => 'Enfermero',               'hospital_id' => null],
            ['full_name' => 'María Fernanda Pérez',   'job_title' => 'Cocinera',                'hospital_id' => null],
            ['full_name' => 'Luis Alberto Gómez',     'job_title' => 'Seguridad',               'hospital_id' => null],
            ['full_name' => 'Ana Luisa Martínez',     'job_title' => 'Recepcionista',           'hospital_id' => null],
            ['full_name' => 'Pedro Ramírez',          'job_title' => 'Camillero',               'hospital_id' => null],
            ['full_name' => 'Carmen Rodríguez',       'job_title' => 'Supervisora de limpieza', 'hospital_id' => null],
            ['full_name' => 'José Antonio Castillo',  'job_title' => 'Auxiliar de bodega',      'hospital_id' => null],
            ['full_name' => 'Daniela Barrios',        'job_title' => 'Nutricionista Asistente', 'hospital_id' => null],
            ['full_name' => 'Ricardo Hernández',      'job_title' => 'Mantenimiento',           'hospital_id' => null],
            ['full_name' => 'Gloria Méndez',          'job_title' => 'Lavandería',              'hospital_id' => null],
        ];

        foreach ($beneficiaries as $b) {
            StaffBeneficiary::create([
                'id'          => (string) Str::uuid(),
                'hospital_id' => $b['hospital_id'],  // puedes cambiar luego por un ID real
                'full_name'   => $b['full_name'],
                'job_title'   => $b['job_title'],
                'status'      => true,
            ]);
        }
    }
}
