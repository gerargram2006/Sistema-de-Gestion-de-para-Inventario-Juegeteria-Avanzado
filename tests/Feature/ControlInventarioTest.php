<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ControlInventarioTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Categoria $categoria;

    private Producto $producto;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->rol(User::ROL_ADMINISTRADOR)->create();
        $this->categoria = Categoria::create(['nombre' => 'Bloques', 'estado' => 'activo']);
        $this->producto = Producto::create([
            'id_categoria' => $this->categoria->id_categoria,
            'sku' => 'JUG-CTL-001',
            'nombre' => 'Set Constructor Espacial',
            'precio' => 110.00,
            'stock_actual' => 10,
            'stock_minimo' => 3,
            'estado' => 'disponible',
        ]);
    }

    public function test_solo_el_administrador_accede_al_control_de_inventario_rf12(): void
    {
        foreach ([User::ROL_VENDEDOR, User::ROL_CAJERO, User::ROL_ALMACENERO] as $rol) {
            $user = User::factory()->rol($rol)->create();

            $this->actingAs($user)->get('/control-inventario')->assertForbidden();
            $this->actingAs($user)->post('/control-inventario/ajuste', [])->assertForbidden();
            $this->actingAs($user)->get('/control-inventario/exportar')->assertForbidden();
        }

        $this->actingAs($this->admin)->get('/control-inventario')->assertOk();
    }

    public function test_registrar_merma_descuenta_stock_y_asienta_kardex_rf14(): void
    {
        $this->actingAs($this->admin)->post('/control-inventario/ajuste', [
            'id_producto' => $this->producto->id_producto,
            'operacion' => 'merma',
            'cantidad_merma' => 3,
            'motivo' => 'Empaque roto durante exhibición',
        ])->assertRedirect('/control-inventario')->assertSessionHasNoErrors();

        $this->assertSame(7, $this->producto->fresh()->stock_actual);
        $this->assertDatabaseHas('movimientos_inventario', [
            'id_producto' => $this->producto->id_producto,
            'tipo' => MovimientoInventario::TIPO_MERMA,
            'cantidad' => 3,
            'stock_anterior' => 10,
            'stock_nuevo' => 7,
            'motivo' => 'Empaque roto durante exhibición',
        ]);
    }

    public function test_no_permite_merma_superior_al_stock_disponible(): void
    {
        $this->actingAs($this->admin)->post('/control-inventario/ajuste', [
            'id_producto' => $this->producto->id_producto,
            'operacion' => 'merma',
            'cantidad_merma' => 25,
            'motivo' => 'Error de conteo',
        ])->assertSessionHasErrors('error');

        $this->assertSame(10, $this->producto->fresh()->stock_actual);
    }

    public function test_auditoria_fisica_recalibra_stock_y_minimo_rf13_rf15(): void
    {
        $this->actingAs($this->admin)->post('/control-inventario/ajuste', [
            'id_producto' => $this->producto->id_producto,
            'operacion' => 'auditoria',
            'stock_fisico' => 14,
            'stock_minimo' => 5,
            'motivo' => 'Conteo físico mensual en bodega',
        ])->assertRedirect('/control-inventario')->assertSessionHasNoErrors();

        $this->producto->refresh();
        $this->assertSame(14, $this->producto->stock_actual);
        $this->assertSame(5, $this->producto->stock_minimo);
        $this->assertDatabaseHas('movimientos_inventario', [
            'id_producto' => $this->producto->id_producto,
            'tipo' => MovimientoInventario::TIPO_AJUSTE_POSITIVO,
            'cantidad' => 4,
            'stock_anterior' => 10,
            'stock_nuevo' => 14,
        ]);
    }

    public function test_operaciones_de_venta_compra_y_devolucion_alimentan_el_kardex(): void
    {
        $cliente = Cliente::create(['dni' => '71002233', 'nombres' => 'Ana Torres']);
        $proveedor = Proveedor::create(['razon_social' => 'Juguetes Andes SAC', 'ruc' => '20555666777', 'activo' => true]);

        // 1. Venta cobrada -> salida_venta
        $this->actingAs($this->admin)->post('/ventas', [
            'id_cliente' => $cliente->id_cliente,
            'tipo_comprobante' => 'boleta',
            'items' => [['id_producto' => $this->producto->id_producto, 'cantidad' => 2]],
        ]);
        $venta = Venta::first();
        $this->actingAs($this->admin)->post("/caja/{$venta->id_venta}/cobrar", [
            'metodo_pago' => 'efectivo',
            'monto_recibido' => 250,
        ]);

        // 2. Compra recibida -> entrada_compra
        $this->actingAs($this->admin)->post('/abastecimiento/orden', [
            'id_proveedor' => $proveedor->id_proveedor,
            'items' => [['id_producto' => $this->producto->id_producto, 'cantidad' => 5, 'precio_costo' => 50]],
        ]);
        $compra = Compra::first();
        $this->actingAs($this->admin)->post("/abastecimiento/orden/{$compra->id_compra}/recibir", [
            'guia_remision' => 'T001-000999',
        ]);

        // 3. Devolución aprobada -> entrada_devolucion
        $this->actingAs($this->admin)->post("/devoluciones/{$venta->id_venta}", [
            'id_producto' => $this->producto->id_producto,
            'cantidad' => 1,
            'tipo' => 'devolucion',
            'decision' => 'aprobar',
            'motivo' => 'Pieza faltante',
        ]);

        $this->assertDatabaseHas('movimientos_inventario', [
            'id_producto' => $this->producto->id_producto,
            'tipo' => MovimientoInventario::TIPO_SALIDA_VENTA,
            'cantidad' => 2,
        ]);
        $this->assertDatabaseHas('movimientos_inventario', [
            'id_producto' => $this->producto->id_producto,
            'tipo' => MovimientoInventario::TIPO_ENTRADA_COMPRA,
            'cantidad' => 5,
        ]);
        $this->assertDatabaseHas('movimientos_inventario', [
            'id_producto' => $this->producto->id_producto,
            'tipo' => MovimientoInventario::TIPO_ENTRADA_DEVOLUCION,
            'cantidad' => 1,
        ]);

        // Exportación CSV (RF-16)
        $export = $this->actingAs($this->admin)->get('/control-inventario/exportar');
        $export->assertOk();
        $this->assertStringContainsString('text/csv', (string) $export->headers->get('content-type'));
        $contenido = $export->streamedContent();
        $this->assertStringContainsString('JUG-CTL-001', $contenido);
        $this->assertStringContainsString('Salida por venta', $contenido);
    }

    public function test_detecta_productos_de_baja_rotacion_rn04(): void
    {
        // Sin ventas, el producto debe figurar en la tabla de baja rotación
        $this->actingAs($this->admin)->get('/control-inventario')
            ->assertOk()
            ->assertSee('Baja rotación')
            ->assertSee('Set Constructor Espacial');
    }
}
