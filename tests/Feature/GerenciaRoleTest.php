<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Role;
use App\Models\User;
use Tests\TestCase;

class GerenciaRoleTest extends TestCase
{
    use RefreshDatabase;
    public function test_gerencia_user_can_access_get_routes(): void
    {
        $role = Role::firstOrCreate(['nombre' => 'GERENCIA']);
        $user = User::factory()->create(['role_id' => $role->id]);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);

        $responseProductos = $this->actingAs($user)->get('/productos');
        $responseProductos->assertStatus(200);

        $responsePnc = $this->actingAs($user)->get('/pnc');
        $responsePnc->assertStatus(200);
    }

    public function test_gerencia_user_is_blocked_on_write_routes_with_403(): void
    {
        $role = Role::firstOrCreate(['nombre' => 'GERENCIA']);
        $user = User::factory()->create(['role_id' => $role->id]);

        // Intentar POST a productos (crear)
        $responsePost = $this->actingAs($user)->post('/productos', [
            'codigo' => 'TEST-01',
            'nombre' => 'Producto Prueba Gerencia',
            'tipo_producto' => 'PREFORMA',
            'unidad_medida' => 'UNIDADES',
        ]);
        $responsePost->assertStatus(403);

        // Intentar DELETE a productos
        $responseDelete = $this->actingAs($user)->delete('/productos/1');
        $responseDelete->assertStatus(403);
    }
}
