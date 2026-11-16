<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_invitados_son_redirigidos_al_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');

        $response = $this->get('/inventario');
        $response->assertRedirect('/login');

        $response = $this->get('/ventas/pos');
        $response->assertRedirect('/login');

        $response = $this->get('/caja');
        $response->assertRedirect('/login');

        $response = $this->get('/abastecimiento');
        $response->assertRedirect('/login');
    }

    public function test_vendedor_puede_acceder_a_pos_pero_no_a_caja_ni_abastecimiento(): void
    {
        $vendedor = User::factory()->create([
            'rol' => User::ROL_VENDEDOR,
            'estado' => 'activo',
        ]);

        $this->actingAs($vendedor)->get('/ventas/pos')->assertOk();
        $this->actingAs($vendedor)->get('/ventas')->assertOk();

        // No debe tener acceso a Caja ni a Abastecimiento
        $this->actingAs($vendedor)->get('/caja')->assertForbidden();
        $this->actingAs($vendedor)->get('/abastecimiento')->assertForbidden();
    }

    public function test_cajero_puede_acceder_a_caja_pero_no_a_pos_ni_abastecimiento(): void
    {
        $cajero = User::factory()->create([
            'rol' => User::ROL_CAJERO,
            'estado' => 'activo',
        ]);

        $this->actingAs($cajero)->get('/caja')->assertOk();

        // No debe tener acceso a POS ni a Abastecimiento
        $this->actingAs($cajero)->get('/ventas/pos')->assertForbidden();
        $this->actingAs($cajero)->get('/abastecimiento')->assertForbidden();
    }

    public function test_almacenero_puede_acceder_a_abastecimiento_pero_no_a_pos_ni_caja(): void
    {
        $almacenero = User::factory()->create([
            'rol' => User::ROL_ALMACENERO,
            'estado' => 'activo',
        ]);

        $this->actingAs($almacenero)->get('/abastecimiento')->assertOk();

        // No debe tener acceso a POS ni a Caja
        $this->actingAs($almacenero)->get('/ventas/pos')->assertForbidden();
        $this->actingAs($almacenero)->get('/caja')->assertForbidden();
    }

    public function test_administrador_tiene_acceso_irrestricto_a_todos_los_modulos(): void
    {
        $admin = User::factory()->create([
            'rol' => User::ROL_ADMINISTRADOR,
            'estado' => 'activo',
        ]);

        $this->actingAs($admin)->get('/dashboard')->assertOk();
        $this->actingAs($admin)->get('/inventario')->assertOk();
        $this->actingAs($admin)->get('/ventas/pos')->assertOk();
        $this->actingAs($admin)->get('/caja')->assertOk();
        $this->actingAs($admin)->get('/abastecimiento')->assertOk();
    }
}
