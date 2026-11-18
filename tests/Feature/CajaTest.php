<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CajaTest extends TestCase
{
    use RefreshDatabase;

    private User $vendedor;

    private User $cajero;

    private Producto $producto;

    private Cliente $cliente;

    protected function setUp(): void
    {
        parent::setUp();

        $this->vendedor = User::factory()->rol(User::ROL_VENDEDOR)->create();
        $this->cajero = User::factory()->rol(User::ROL_CAJERO)->create();

        $categoria = Categoria::create(['nombre' => 'Peluches', 'estado' => 'activo']);

        $this->producto = Producto::create([
            'id_categoria' => $categoria->id_categoria,
            'sku' => 'JUG-PEL-010',
            'nombre' => 'Oso de Peluche Mediano',
            'precio' => 40.00,
            'stock_actual' => 20,
            'stock_minimo' => 3,
            'estado' => 'disponible',
        ]);

        $this->cliente = Cliente::create(['dni' => '70112233', 'nombres' => 'Lucía Mamani']);
    }

    private function generarTicket(string $tipoComprobante, int $cantidad = 1): Venta
    {
        $this->actingAs($this->vendedor)->post('/ventas', [
            'id_cliente' => $this->cliente->id_cliente,
            'tipo_comprobante' => $tipoComprobante,
            'items' => [['id_producto' => $this->producto->id_producto, 'cantidad' => $cantidad]],
        ]);

        return Venta::latest('id_venta')->first();
    }

    private function cobrar(Venta $venta)
    {
        return $this->actingAs($this->cajero)->post("/caja/{$venta->id_venta}/cobrar", [
            'metodo_pago' => 'efectivo',
            'monto_recibido' => 500,
        ]);
    }

    public function test_cada_serie_de_comprobante_tiene_su_propio_correlativo(): void
    {
        $boleta1 = $this->generarTicket('boleta');
        $boleta2 = $this->generarTicket('boleta');
        $factura = $this->generarTicket('factura');

        $this->cobrar($boleta1);
        $this->cobrar($boleta2);
        $this->cobrar($factura);

        $this->assertSame('B001-000001', $boleta1->fresh()->nro_comprob);
        $this->assertSame('B001-000002', $boleta2->fresh()->nro_comprob);
        // Antes la factura salia F001-000003 porque se usaba el id de la venta
        $this->assertSame('F001-000001', $factura->fresh()->nro_comprob);
    }

    public function test_un_ticket_no_se_puede_cobrar_dos_veces(): void
    {
        $venta = $this->generarTicket('boleta', 3);

        $this->cobrar($venta)->assertRedirect(route('caja.comprobante', $venta->id_venta));
        $this->cobrar($venta)->assertSessionHasErrors('error');

        // El stock se descuenta una sola vez: 20 - 3
        $this->assertSame(17, $this->producto->fresh()->stock_actual);
        $this->assertSame('B001-000001', $venta->fresh()->nro_comprob);
    }

    public function test_no_se_cobra_si_el_stock_se_agoto_despues_de_emitir_el_ticket(): void
    {
        $venta = $this->generarTicket('boleta', 5);

        // Otra venta ya cobrada dejo el stock por debajo de lo pedido
        $this->producto->update(['stock_actual' => 2]);

        $this->cobrar($venta)->assertSessionHasErrors('error');

        $this->assertSame('pendiente', $venta->fresh()->estado);
        $this->assertNull($venta->fresh()->nro_comprob);
        $this->assertSame(2, $this->producto->fresh()->stock_actual);
    }
}
