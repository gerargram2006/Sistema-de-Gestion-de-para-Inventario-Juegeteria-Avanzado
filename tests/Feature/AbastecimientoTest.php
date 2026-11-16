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
}
