<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Compra;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbastecimientoTest extends TestCase
{
    use RefreshDatabase;

    public function test_almacenero_puede_crear_orden_de_compra_y_recibir_mercaderia(): void
    {
        $almacenero = User::factory()->create([
            'rol' => User::ROL_ALMACENERO,
            'estado' => 'activo',
        ]);

        $categoria = Categoria::create(['nombre' => 'Educativos', 'estado' => 'activo']);

        $producto = Producto::create([
            'id_categoria' => $categoria->id_categoria,
            'sku' => 'JUG-EDU-001',
            'nombre' => 'Microscopio Infantil',
            'precio' => 120.00,
            'stock_actual' => 2,
            'stock_minimo' => 5,
            'estado' => 'stock bajo',
        ]);

        $proveedor = Proveedor::create([
            'razon_social' => 'Distribuidora Didáctica SAC',
            'ruc' => '20123456789',
            'activo' => true,
        ]);

        // 1. Crear Orden de Compra
        $response = $this->actingAs($almacenero)->post('/abastecimiento/orden', [
            'id_proveedor' => $proveedor->id_proveedor,
            'items' => [
                [
                    'id_producto' => $producto->id_producto,
                    'cantidad' => 15,
                    'precio_costo' => 60.00,
                ],
            ],
        ]);

        $response->assertRedirect('/abastecimiento');

        $this->assertDatabaseHas('compras', [
            'id_proveedor' => $proveedor->id_proveedor,
            'proveedor' => 'Distribuidora Didáctica SAC',
            'total' => 900.00,
        ]);

        $compra = Compra::first();
        $this->assertMatchesRegularExpression('/^OC-\d{8}-\d{6}$/', $compra->nro_orden);

        // 2. Recepción de mercadería en almacén
        $responseRecibir = $this->actingAs($almacenero)->post("/abastecimiento/orden/{$compra->id_compra}/recibir");
        $responseRecibir->assertRedirect('/abastecimiento');

        $compra->refresh();
        $this->assertEquals('recibida', $compra->estado);

        // El stock debe incrementarse de 2 a 17 (2 + 15)
        $producto->refresh();
        $this->assertEquals(17, $producto->stock_actual);
        $this->assertEquals('disponible', $producto->estado);
    }

    public function test_una_orden_cancelada_o_ya_recibida_no_suma_stock(): void
    {
        $almacenero = User::factory()->rol(User::ROL_ALMACENERO)->create();
        $categoria = Categoria::create(['nombre' => 'Vehículos', 'estado' => 'activo']);
        $producto = Producto::create([
            'id_categoria' => $categoria->id_categoria,
            'sku' => 'JUG-VEH-001',
            'nombre' => 'Camión de Bomberos',
            'precio' => 95.00,
            'stock_actual' => 4,
            'stock_minimo' => 5,
            'estado' => 'stock bajo',
        ]);

        $proveedor = Proveedor::create([
            'razon_social' => 'Importadora Lima SAC',
            'ruc' => '20123456790',
            'activo' => true,
        ]);

        $crearOrden = fn () => $this->actingAs($almacenero)->post('/abastecimiento/orden', [
            'id_proveedor' => $proveedor->id_proveedor,
            'items' => [['id_producto' => $producto->id_producto, 'cantidad' => 10, 'precio_costo' => 50]],
        ]);

        // Orden cancelada: no debe poder recibirse
        $crearOrden();
        $cancelada = Compra::latest('id_compra')->first();
        $cancelada->update(['estado' => 'cancelada']);

        $this->actingAs($almacenero)
            ->post("/abastecimiento/orden/{$cancelada->id_compra}/recibir")
            ->assertSessionHasErrors('error');
        $this->assertSame(4, $producto->fresh()->stock_actual);

        // Orden valida recibida dos veces: el stock sube una sola vez
        $crearOrden();
        $orden = Compra::latest('id_compra')->first();
        $this->actingAs($almacenero)->post("/abastecimiento/orden/{$orden->id_compra}/recibir");
        $this->actingAs($almacenero)
            ->post("/abastecimiento/orden/{$orden->id_compra}/recibir")
            ->assertSessionHasErrors('error');

        $this->assertSame(14, $producto->fresh()->stock_actual);
        $this->assertSame('cancelada', $cancelada->fresh()->estado);
    }

    public function test_la_alerta_de_stock_critico_precarga_el_producto_en_la_orden(): void
    {
        $almacenero = User::factory()->rol(User::ROL_ALMACENERO)->create();
        $categoria = Categoria::create(['nombre' => 'Primera infancia', 'estado' => 'activo']);
        $producto = Producto::create([
            'id_categoria' => $categoria->id_categoria,
            'sku' => 'JUG-PRI-001',
            'nombre' => 'Cry Babies Bebé Llorón Lala',
            'precio' => 129.00,
            'stock_actual' => 2,
            'stock_minimo' => 6,
            'estado' => 'stock bajo',
        ]);

        $this->actingAs($almacenero)->get('/abastecimiento')
            ->assertOk()
            ->assertSee('precargarOrden('.$producto->id_producto, false)
            ->assertSee('Clic para precargar');
    }

    public function test_no_se_puede_emitir_una_orden_a_un_proveedor_inactivo(): void
    {
        $almacenero = User::factory()->rol(User::ROL_ALMACENERO)->create();
        $categoria = Categoria::create(['nombre' => 'Didácticos', 'estado' => 'activo']);
        $producto = Producto::create([
            'id_categoria' => $categoria->id_categoria,
            'sku' => 'JUG-DID-001',
            'nombre' => 'Ábaco de Madera',
            'precio' => 45.00,
            'stock_actual' => 1,
            'stock_minimo' => 4,
            'estado' => 'stock bajo',
        ]);
        $proveedor = Proveedor::create([
            'razon_social' => 'Proveedor Inactivo SAC',
            'ruc' => '20123456789',
            'activo' => false,
        ]);

        $this->actingAs($almacenero)->post('/abastecimiento/orden', [
            'id_proveedor' => $proveedor->id_proveedor,
            'items' => [['id_producto' => $producto->id_producto, 'cantidad' => 5, 'precio_costo' => 20]],
        ])->assertSessionHasErrors('id_proveedor');

        $this->assertDatabaseCount('compras', 0);
    }
}
