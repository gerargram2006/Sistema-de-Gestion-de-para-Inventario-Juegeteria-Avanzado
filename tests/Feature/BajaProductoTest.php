<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BajaProductoTest extends TestCase
{
    use RefreshDatabase;

    private function producto(int $stock, int $minimo = 5): Producto
    {
        $categoria = Categoria::create(['nombre' => 'Peluches', 'estado' => 'activo']);

        $producto = new Producto([
            'id_categoria' => $categoria->id_categoria,
            'sku' => 'PEL-' . $stock,
            'nombre' => 'Oso de Peluche ' . $stock,
            'precio' => 39.90,
            'stock_actual' => $stock,
            'stock_minimo' => $minimo,
        ]);
        $producto->actualizarEstadoStock();
        $producto->save();

        return $producto;
    }

    public function test_almacenero_da_de_baja_y_reactiva_un_juguete(): void
    {
        $almacenero = User::factory()->rol(User::ROL_ALMACENERO)->create();
        $producto = $this->producto(stock: 2);

        $this->actingAs($almacenero)->patch(route('inventario.estado', $producto))->assertRedirect();
        $this->assertSame('inactivo', $producto->fresh()->estado);

        // Al reactivarlo se recalcula el estado segun el stock (2 <= 5)
        $this->actingAs($almacenero)->patch(route('inventario.estado', $producto))->assertRedirect();
        $this->assertSame('stock bajo', $producto->fresh()->estado);
    }

    public function test_juguete_inactivo_no_genera_alerta_de_reposicion(): void
    {
        $admin = User::factory()->rol(User::ROL_ADMINISTRADOR)->create();
        $critico = $this->producto(stock: 1);
        $this->producto(stock: 0)->forceFill(['estado' => 'inactivo'])->save();

        $this->assertSame([$critico->id_producto], Producto::stockCritico()->pluck('id_producto')->all());

        $this->actingAs($admin)->get('/inventario')->assertOk()->assertSee('Inactivo');
    }

    public function test_vendedor_no_puede_dar_de_baja_juguetes(): void
    {
        $vendedor = User::factory()->rol(User::ROL_VENDEDOR)->create();
        $producto = $this->producto(stock: 10);

        $this->actingAs($vendedor)->patch(route('inventario.estado', $producto))->assertForbidden();
        $this->assertSame('disponible', $producto->fresh()->estado);
    }
}
