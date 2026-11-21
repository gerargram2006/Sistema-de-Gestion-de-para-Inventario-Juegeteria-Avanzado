<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Devolucion;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DevolucionTest extends TestCase
{
    use RefreshDatabase;

    private User $vendedor;

    private User $cajero;

    private Producto $producto;

    protected function setUp(): void
    {
        parent::setUp();

        $this->vendedor = User::factory()->rol(User::ROL_VENDEDOR)->create();
        $this->cajero = User::factory()->rol(User::ROL_CAJERO)->create();

        $categoria = Categoria::create(['nombre' => 'Vehículos y Pistas', 'estado' => 'activo']);
        $this->producto = Producto::create([
            'id_categoria' => $categoria->id_categoria,
            'sku' => 'JUG-VEH-050',
            'nombre' => 'Auto a Control Remoto',
            'precio' => 120.00,
            'stock_actual' => 10,
            'stock_minimo' => 2,
            'estado' => 'disponible',
        ]);
    }

    /**
     * Venta de 3 unidades cobrada en caja: el stock queda en 7.
     */
    private function ventaPagada(): Venta
    {
        $cliente = Cliente::create(['dni' => '41526374', 'nombres' => 'Jorge Ccama']);

        $this->actingAs($this->vendedor)->post('/ventas', [
            'id_cliente' => $cliente->id_cliente,
            'tipo_comprobante' => 'boleta',
            'metodo_pago' => 'efectivo',
            'items' => [['id_producto' => $this->producto->id_producto, 'cantidad' => 3]],
        ]);

        $venta = Venta::first();
        $this->actingAs($this->cajero)->post("/caja/{$venta->id_venta}/cobrar", [
            'metodo_pago' => 'efectivo',
            'monto_recibido' => 360,
        ]);

        return $venta->fresh();
    }

    private function registrar(Venta $venta, array $datos)
    {
        return $this->actingAs($this->cajero)->post("/devoluciones/{$venta->id_venta}", $datos + [
            'id_producto' => $this->producto->id_producto,
            'cantidad' => 1,
            'tipo' => Devolucion::TIPO_DEVOLUCION,
            'decision' => 'aprobar',
        ]);
    }

    public function test_se_busca_la_venta_por_su_ticket_o_comprobante(): void
    {
        $venta = $this->ventaPagada();

        $this->actingAs($this->cajero)->get('/devoluciones?buscar=' . $venta->codigo_venta)
            ->assertOk()->assertSee('Auto a Control Remoto');

        $this->actingAs($this->cajero)->get('/devoluciones?buscar=' . $venta->nro_comprob)
            ->assertOk()->assertSee($venta->codigo_venta);
    }

    public function test_la_devolucion_aprobada_reingresa_el_juguete_al_inventario(): void
    {
        $venta = $this->ventaPagada();

        $this->registrar($venta, ['cantidad' => 2])->assertSessionHasNoErrors();

        $this->assertSame(9, $this->producto->fresh()->stock_actual);
        $this->assertDatabaseHas('devoluciones', [
            'id_venta' => $venta->id_venta,
            'cantidad' => 2,
            'estado' => Devolucion::ESTADO_REEMBOLSADA,
            'id_usuario' => $this->cajero->id,
        ]);
        $this->assertSame('pagada', $venta->fresh()->estado);
    }

    public function test_el_cambio_entrega_una_unidad_nueva_del_stock(): void
    {
        $venta = $this->ventaPagada();

        $this->registrar($venta, ['tipo' => Devolucion::TIPO_CAMBIO, 'motivo' => 'Falla de fábrica'])
            ->assertSessionHasNoErrors();

        // La unidad defectuosa no vuelve a la venta; se entrega otra: 7 - 1
        $this->assertSame(6, $this->producto->fresh()->stock_actual);
        $this->assertDatabaseHas('devoluciones', ['estado' => Devolucion::ESTADO_CAMBIADA]);
    }

    public function test_no_se_puede_devolver_mas_de_lo_vendido(): void
    {
        $venta = $this->ventaPagada();

        $this->registrar($venta, ['cantidad' => 2])->assertSessionHasNoErrors();
        $this->registrar($venta, ['cantidad' => 2])->assertSessionHasErrors('error');

        $this->assertSame(9, $this->producto->fresh()->stock_actual);
    }

    public function test_al_reembolsar_todas_las_unidades_la_venta_queda_devuelta(): void
    {
        $venta = $this->ventaPagada();

        $this->registrar($venta, ['cantidad' => 3])->assertSessionHasNoErrors();

        $this->assertSame('devuelta', $venta->fresh()->estado);
        $this->assertSame(10, $this->producto->fresh()->stock_actual);
    }

    public function test_fuera_de_plazo_no_se_aprueba_pero_si_se_registra_el_rechazo(): void
    {
        $venta = $this->ventaPagada();
        $venta->update(['fecha' => now()->subDays(Devolucion::PLAZO_DIAS + 1)]);

        $this->registrar($venta, [])->assertSessionHasErrors('error');
        $this->assertSame(7, $this->producto->fresh()->stock_actual);

        // El rechazo exige motivo y no modifica el inventario
        $this->registrar($venta, ['decision' => 'rechazar'])->assertSessionHasErrors('motivo');
        $this->registrar($venta, ['decision' => 'rechazar', 'motivo' => 'Fuera de plazo'])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('devoluciones', ['estado' => Devolucion::ESTADO_RECHAZADA, 'motivo' => 'Fuera de plazo']);
        $this->assertSame(7, $this->producto->fresh()->stock_actual);
    }

    public function test_no_se_atienden_ventas_pendientes_de_cobro(): void
    {
        $cliente = Cliente::create(['dni' => '41526375', 'nombres' => 'Ana Ccori']);
        $this->actingAs($this->vendedor)->post('/ventas', [
            'id_cliente' => $cliente->id_cliente,
            'tipo_comprobante' => 'boleta',
            'metodo_pago' => 'efectivo',
            'items' => [['id_producto' => $this->producto->id_producto, 'cantidad' => 1]],
        ]);

        $this->registrar(Venta::first(), [])->assertSessionHasErrors('error');
        $this->assertDatabaseCount('devoluciones', 0);
    }

    public function test_el_almacenero_no_accede_a_devoluciones(): void
    {
        $almacenero = User::factory()->rol(User::ROL_ALMACENERO)->create();

        $this->actingAs($almacenero)->get('/devoluciones')->assertForbidden();
        $this->actingAs($this->vendedor)->get('/devoluciones')->assertOk();
    }
}
