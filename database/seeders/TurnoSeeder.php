<?php

namespace Database\Seeders;

use App\Models\Turno;
use Illuminate\Database\Seeder;

class TurnoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $turnos = [
            [
                'nombre' => 'DÍA',
                'hora_inicio' => '07:00:00',
                'hora_fin' => '19:00:00',
            ],
            [
                'nombre' => 'NOCHE',
                'hora_inicio' => '19:00:00',
                'hora_fin' => '07:00:00',
            ],
        ];

        foreach ($turnos as $turno) {
            Turno::firstOrCreate(
                ['nombre' => $turno['nombre']],
                [
                    'hora_inicio' => $turno['hora_inicio'],
                    'hora_fin' => $turno['hora_fin'],
                ]
            );
        }
    }
}
