<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where('nombre', 'ADMINISTRADOR')->first();

        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrador Grupo Fénix',
                'email' => 'admin@grupofenix.com',
                'password' => Hash::make('password123'),
                'role_id' => $adminRole?->id,
            ]
        );
    }
}
