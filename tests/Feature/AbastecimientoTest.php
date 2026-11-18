<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Compra;
use App\Models\Producto;
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

        // 1. Crear Orden de Compra
        $response = $this->actingAs($almacenero)->post('/abastecimiento/orden', [
            'proveedor' => 'Distribuidora Didáctica SAC',
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

        $crearOrden = fn () => $this->actingAs($almacenero)->post('/abastecimiento/orden', [
            'proveedor' => 'Importadora Lima SAC',
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
}
