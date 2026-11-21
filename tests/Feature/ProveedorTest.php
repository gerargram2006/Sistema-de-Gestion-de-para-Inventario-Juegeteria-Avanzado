<?php

namespace Tests\Feature;

use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProveedorTest extends TestCase
{
    use RefreshDatabase;

    private function administrador(): User
    {
        return User::factory()->rol(User::ROL_ADMINISTRADOR)->create();
    }

    public function test_administrador_puede_registrar_y_listar_proveedores(): void
    {
        $admin = $this->administrador();

        $this->actingAs($admin)->get(route('proveedores.index'))->assertOk();

        $this->actingAs($admin)->post(route('proveedores.store'), [
            'razon_social' => 'Mattel Perú S.A.',
            'ruc' => '20123456789',
            'telefono' => '01 555 0101',
            'email' => 'ventas@mattel.test',
            'direccion' => 'Av. Javier Prado 1234',
        ])->assertRedirect(route('proveedores.index'));

        $this->assertDatabaseHas('proveedores', [
            'razon_social' => 'Mattel Perú S.A.',
            'ruc' => '20123456789',
            'activo' => true,
        ]);

        $this->actingAs($admin)->get(route('proveedores.index'))
            ->assertOk()
            ->assertSee('Mattel Perú S.A.');
    }

    public function test_almacenero_puede_registrar_un_proveedor_rapido_desde_abastecimiento(): void
    {
        $almacenero = User::factory()->rol(User::ROL_ALMACENERO)->create();

        $this->actingAs($almacenero)
            ->postJson(route('abastecimiento.proveedores.rapido'), [
                'razon_social' => 'Juguetes Andinos S.A.C.',
                'ruc' => '20123456789',
                'activo' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('proveedor.razon_social', 'Juguetes Andinos S.A.C.');

        $this->assertDatabaseHas('proveedores', [
            'razon_social' => 'Juguetes Andinos S.A.C.',
            'ruc' => '20123456789',
            'activo' => true,
        ]);
    }

    public function test_el_alta_rapida_valida_el_ruc_y_el_estado_autorizado(): void
    {
        $almacenero = User::factory()->rol(User::ROL_ALMACENERO)->create();

        $this->actingAs($almacenero)
            ->postJson(route('abastecimiento.proveedores.rapido'), [
                'razon_social' => 'Proveedor sin RUC válido',
                'ruc' => '123',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('ruc');

        $this->actingAs($almacenero)
            ->postJson(route('abastecimiento.proveedores.rapido'), [
                'razon_social' => 'Proveedor suspendido S.A.C.',
                'ruc' => '20123456789',
                'activo' => false,
            ])
            ->assertCreated();

        $this->assertDatabaseHas('proveedores', [
            'ruc' => '20123456789',
            'activo' => false,
        ]);
    }

    public function test_no_se_registran_dos_proveedores_con_el_mismo_ruc(): void
    {
        $admin = $this->administrador();

        Proveedor::create([
            'razon_social' => 'Distribuidora Juguetes del Sur S.A.C.',
            'ruc' => '20123456789',
            'activo' => true,
        ]);

        $this->actingAs($admin)->post(route('proveedores.store'), [
            'razon_social' => 'Otra razón social',
            'ruc' => '20123456789',
        ])->assertSessionHasErrors('ruc');

        $this->assertDatabaseCount('proveedores', 1);
    }

    public function test_administrador_puede_actualizar_y_cambiar_el_estado_de_un_proveedor(): void
    {
        $admin = $this->administrador();
        $proveedor = Proveedor::create([
            'razon_social' => 'Importaciones Lúdica E.I.R.L.',
            'ruc' => '20123456789',
            'activo' => true,
        ]);

        $this->actingAs($admin)->put(route('proveedores.update', $proveedor), [
            'razon_social' => 'Importaciones Lúdica del Perú E.I.R.L.',
            'ruc' => '20123456789',
            'telefono' => '01 555 0103',
        ])->assertRedirect(route('proveedores.index'));

        $this->assertSame('Importaciones Lúdica del Perú E.I.R.L.', $proveedor->fresh()->razon_social);

        $this->actingAs($admin)->patch(route('proveedores.estado', $proveedor))
            ->assertRedirect();

        $this->assertFalse($proveedor->fresh()->activo);

        $this->actingAs($admin)->patch(route('proveedores.estado', $proveedor));
        $this->assertTrue($proveedor->fresh()->activo);
    }

    public function test_solo_el_administrador_gestiona_proveedores(): void
    {
        $vendedor = User::factory()->rol(User::ROL_VENDEDOR)->create();
        $almacenero = User::factory()->rol(User::ROL_ALMACENERO)->create();

        $this->actingAs($vendedor)->get(route('proveedores.index'))->assertForbidden();
        $this->actingAs($almacenero)->get(route('proveedores.index'))->assertForbidden();

        $this->actingAs($almacenero)->post(route('proveedores.store'), [
            'razon_social' => 'Proveedor no autorizado',
            'ruc' => '20123456789',
        ])->assertForbidden();

        $this->assertDatabaseCount('proveedores', 0);
    }
}
