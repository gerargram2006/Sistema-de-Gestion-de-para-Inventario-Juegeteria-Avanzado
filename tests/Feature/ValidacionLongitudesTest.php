<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Las reglas de validacion deben respetar el tamano de las columnas en MySQL:
 * en modo estricto un texto mas largo que la columna provoca un error 500
 * ("Data too long"), mientras que SQLite lo acepta en silencio.
 */
class ValidacionLongitudesTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_telefono_del_cliente_no_supera_la_columna_de_15_caracteres(): void
    {
        $vendedor = User::factory()->rol(User::ROL_VENDEDOR)->create();

        $this->actingAs($vendedor)->post('/ventas/cliente', [
            'dni' => '40506070',
            'nombre' => 'Carmen Rojas',
            'telefono' => '+51 999 888 777 66', // 18 caracteres
        ])->assertSessionHasErrors('telefono');

        $this->assertDatabaseMissing('clientes', ['dni' => '40506070']);
    }

    public function test_la_razon_social_del_proveedor_no_supera_la_columna_de_150_caracteres(): void
    {
        $admin = User::factory()->rol(User::ROL_ADMINISTRADOR)->create();

        $this->actingAs($admin)->post('/proveedores', [
            'razon_social' => str_repeat('Distribuidora ', 12), // 156 caracteres
            'ruc' => '20123456789',
        ])->assertSessionHasErrors('razon_social');

        $this->assertDatabaseMissing('proveedores', ['ruc' => '20123456789']);
    }
}
