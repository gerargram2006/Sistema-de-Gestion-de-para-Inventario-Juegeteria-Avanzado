<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\Devolucion;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlujosLogicosYDocumentosTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Categoria $categoria;

    private Producto $producto;

    private Cliente $cliente;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->rol(User::ROL_ADMINISTRADOR)->create();
        $this->categoria = Categoria::create(['nombre' => 'Juegos de Mesa', 'estado' => 'activo']);
        $this->producto = Producto::create([
            'id_categoria' => $this->categoria->id_categoria,
            'sku' => 'JUG-FLJ-001',
            'nombre' => 'Ajedrez Magnético',
            'precio' => 50.00,
            'stock_actual' => 12,
            'stock_minimo' => 3,
            'estado' => 'disponible',
        ]);
        $this->cliente = Cliente::create(['dni' => '44556677', 'nombres' => 'Luis Paredes']);
    }

    public function test_botones_y_modales_tienen_ambito_alpine_activo(): void
    {
        $this->actingAs($this->admin)->get('/inventario')
            ->assertOk()
            ->assertSee('x-data', false)
            ->assertSee("open-modal', 'modal-crear-producto'", false)
            ->assertDontSee('<template x-if="show">', false);

        $this->actingAs($this->admin)->get('/usuarios')
            ->assertOk()
            ->assertSee("open-modal', 'modal-crear-usuario'", false);

        $this->actingAs($this->admin)->get('/ventas/pos')
            ->assertOk()
            ->assertSee("open-modal', 'modal-nuevo-cliente'", false);
    }

    public function test_registro_rapido_de_cliente_por_json_preserva_flujo_pos(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/ventas/cliente', [
            'dni' => '73112233',
            'nombre' => 'Sofía Quispe',
            'telefono' => '987111222',
            'email' => 'sofia@correo.pe',
        ]);

        $response->assertCreated()
            ->assertJsonPath('cliente.dni', '73112233')
            ->assertJsonPath('cliente.nombre', 'Sofía Quispe');

        $this->assertDatabaseHas('clientes', ['dni' => '73112233', 'nombres' => 'Sofía Quispe']);
    }

    public function test_se_puede_anular_ticket_pendiente_pero_no_uno_pagado(): void
    {
        $this->actingAs($this->admin)->post('/ventas', [
            'id_cliente' => $this->cliente->id_cliente,
            'tipo_comprobante' => 'boleta',
            'items' => [['id_producto' => $this->producto->id_producto, 'cantidad' => 2]],
        ]);
        $venta = Venta::first();

        $this->actingAs($this->admin)->patch("/ventas/{$venta->id_venta}/anular")
            ->assertSessionHasNoErrors();

        $this->assertSame('anulada', $venta->fresh()->estado);

        // Un ticket ya cobrado no debe poder anularse
        $this->actingAs($this->admin)->post('/ventas', [
            'id_cliente' => $this->cliente->id_cliente,
            'tipo_comprobante' => 'factura',
            'items' => [['id_producto' => $this->producto->id_producto, 'cantidad' => 1]],
        ]);
        $pagada = Venta::latest('id_venta')->first();
        $this->actingAs($this->admin)->post("/caja/{$pagada->id_venta}/cobrar", [
            'metodo_pago' => 'efectivo',
            'monto_recibido' => 100,
        ]);

        $this->actingAs($this->admin)->patch("/ventas/{$pagada->id_venta}/anular")
            ->assertSessionHasErrors('error');
        $this->assertSame('pagada', $pagada->fresh()->estado);
    }

    public function test_caja_persiste_monto_recibido_y_vuelto_y_muestra_resumen_diario_rf23(): void
    {
        $this->actingAs($this->admin)->post('/ventas', [
            'id_cliente' => $this->cliente->id_cliente,
            'tipo_comprobante' => 'factura',
            'items' => [['id_producto' => $this->producto->id_producto, 'cantidad' => 2]],
        ]);
        $venta = Venta::first();

        $this->actingAs($this->admin)->post("/caja/{$venta->id_venta}/cobrar", [
            'metodo_pago' => 'efectivo',
            'monto_recibido' => 120,
        ])->assertRedirect(route('caja.comprobante', $venta->id_venta));

        $venta->refresh();
        $this->assertEquals(120.00, (float) $venta->monto_recibido);
        $this->assertEquals(20.00, (float) $venta->vuelto);

        $this->actingAs($this->admin)->get(route('caja.comprobante', $venta->id_venta))
            ->assertOk()
            ->assertSee('FACTURA ELECTRÓNICA')
            ->assertSee('Representación impresa de Factura Electrónica')
            ->assertSee('120.00')
            ->assertSee('20.00');

        $this->actingAs($this->admin)->get('/caja')
            ->assertOk()
            ->assertSee('Resumen de Caja del Día (RF-23)')
            ->assertSee('100.00');
    }

    public function test_abastecimiento_soporta_guia_remision_constancia_cancelacion_y_alerta_rn01(): void
    {
        $proveedor = Proveedor::create(['razon_social' => 'Distribuidora Sur SAC', 'ruc' => '20999888777', 'activo' => true]);

        // Orden 1: recibir con guía de remisión y ver constancia (RF-25 / RF-30)
        $this->actingAs($this->admin)->post('/abastecimiento/orden', [
            'id_proveedor' => $proveedor->id_proveedor,
            'items' => [['id_producto' => $this->producto->id_producto, 'cantidad' => 4, 'precio_costo' => 20]],
        ]);
        $orden1 = Compra::first();

        $this->actingAs($this->admin)->post("/abastecimiento/orden/{$orden1->id_compra}/recibir", [
            'guia_remision' => 'T001-004321',
            'observaciones_recepcion' => 'Llegó conforme con empaque sellado',
        ])->assertRedirect('/abastecimiento');

        $orden1->refresh();
        $this->assertSame('T001-004321', $orden1->guia_remision);
        $this->assertNotNull($orden1->fecha_recepcion);

        $this->actingAs($this->admin)->get("/abastecimiento/orden/{$orden1->id_compra}/constancia")
            ->assertOk()
            ->assertSee('T001-004321')
            ->assertSee('Llegó conforme con empaque sellado');

        // Orden 2: retrasada > 72h (RN-01) y luego cancelada
        $this->actingAs($this->admin)->post('/abastecimiento/orden', [
            'id_proveedor' => $proveedor->id_proveedor,
            'items' => [['id_producto' => $this->producto->id_producto, 'cantidad' => 2, 'precio_costo' => 20]],
        ]);
        $orden2 = Compra::latest('id_compra')->first();
        $orden2->update(['fecha' => now()->subHours(80)]);

        $this->assertTrue($orden2->fresh()->estaRetrasada());
        $this->actingAs($this->admin)->get('/dashboard')->assertOk()->assertSee('RN-01');

        $this->actingAs($this->admin)->patch("/abastecimiento/orden/{$orden2->id_compra}/cancelar")
            ->assertRedirect('/abastecimiento');
        $this->assertSame('cancelada', $orden2->fresh()->estado);
    }

    public function test_devolucion_aprobada_genera_constancia_y_clientes_muestra_historial_rf21_rf27(): void
    {
        $this->actingAs($this->admin)->post('/ventas', [
            'id_cliente' => $this->cliente->id_cliente,
            'tipo_comprobante' => 'boleta',
            'items' => [['id_producto' => $this->producto->id_producto, 'cantidad' => 2]],
        ]);
        $venta = Venta::first();
        $this->actingAs($this->admin)->post("/caja/{$venta->id_venta}/cobrar", [
            'metodo_pago' => 'yape',
        ]);

        // Historial de clientes (RF-27)
        $this->actingAs($this->admin)->get('/ventas/clientes?buscar=44556677')
            ->assertOk()
            ->assertSee('Luis Paredes')
            ->assertSee($venta->codigo_venta);

        // Nota de crédito imprimible (RF-21)
        $this->actingAs($this->admin)->post("/devoluciones/{$venta->id_venta}", [
            'id_producto' => $this->producto->id_producto,
            'cantidad' => 1,
            'tipo' => Devolucion::TIPO_DEVOLUCION,
            'decision' => 'aprobar',
            'motivo' => 'Tablero doblado',
        ]);
        $dev = Devolucion::first();

        $this->actingAs($this->admin)->get("/devoluciones/{$dev->id_devolucion}/constancia")
            ->assertOk()
            ->assertSee('NOTA DE CRÉDITO ELECTRÓNICA')
            ->assertSee('Tablero doblado');
    }

    public function test_se_pueden_crear_y_desactivar_categorias_en_inventario(): void
    {
        $this->actingAs($this->admin)->post('/inventario/categorias', [
            'nombre_categoria' => 'Educativos STEM',
            'descripcion_categoria' => 'Ciencia y robótica infantil',
        ])->assertRedirect('/inventario');

        $cat = Categoria::where('nombre', 'Educativos STEM')->firstOrFail();
        $this->assertSame('activo', $cat->estado);

        $this->actingAs($this->admin)->patch("/inventario/categorias/{$cat->id_categoria}/estado")
            ->assertSessionHasNoErrors();
        $this->assertSame('inactivo', $cat->fresh()->estado);
    }
}
