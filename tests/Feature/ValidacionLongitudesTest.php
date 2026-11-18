<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
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

    public function test_el_proveedor_no_supera_la_columna_de_100_caracteres(): void
    {
        $almacenero = User::factory()->rol(User::ROL_ALMACENERO)->create();
        $categoria = Categoria::create(['nombre' => 'Bloques', 'estado' => 'activo']);
        $producto = Producto::create([
            'id_categoria' => $categoria->id_categoria,
            'sku' => 'JUG-BLO-100',
            'nombre' => 'Bloques de Madera',
            'precio' => 35.00,
            'stock_actual' => 1,
            'stock_minimo' => 4,
            'estado' => 'stock bajo',
        ]);

        $this->actingAs($almacenero)->post('/abastecimiento/orden', [
            'proveedor' => str_repeat('Distribuidora ', 8), // 112 caracteres
            'items' => [['id_producto' => $producto->id_producto, 'cantidad' => 5, 'precio_costo' => 20]],
        ])->assertSessionHasErrors('proveedor');

        $this->assertDatabaseCount('compras', 0);
    }
}
