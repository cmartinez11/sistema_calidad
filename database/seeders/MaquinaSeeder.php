<?php

namespace Database\Seeders;

use App\Models\Maquina;
use Illuminate\Database\Seeder;

class MaquinaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $maquinas = [
            ['codigo' => 'M01', 'nombre' => 'INY-01', 'estado' => 'activo'],
            ['codigo' => 'M02', 'nombre' => 'INY-02', 'estado' => 'activo'],
            ['codigo' => 'M03', 'nombre' => 'INY-03', 'estado' => 'activo'],
            ['codigo' => 'M04', 'nombre' => 'INY-04', 'estado' => 'activo'],
            ['codigo' => 'M05', 'nombre' => 'INY-05', 'estado' => 'activo'],
        ];

        foreach ($maquinas as $maquina) {
            Maquina::firstOrCreate(
                ['codigo' => $maquina['codigo']],
                [
                    'nombre' => $maquina['nombre'],
                    'estado' => $maquina['estado'],
                ]
            );
        }
    }
}
