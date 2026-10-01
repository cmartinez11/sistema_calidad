<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['nombre' => 'ADMINISTRADOR'],
            ['nombre' => 'SUPERVISOR'],
            ['nombre' => 'USUARIO'],
            ['nombre' => 'GERENCIA'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['nombre' => $role['nombre']]
                //['descripcion' => $role['descripcion']]
            );
        }
    }
}
