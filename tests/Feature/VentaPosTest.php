<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VentaPosTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendedor_puede_generar_ticket_de_venta_y_queda_pendiente(): void
    {
        $vendedor = User::factory()->create([
            'rol' => User::ROL_VENDEDOR,
            'estado' => 'activo',
        ]);

        $categoria = Categoria::create([
            'nombre' => 'Pistas y Vehículos',
            'estado' => 'activo',
        ]);

        $producto = Producto::create([
            'id_categoria' => $categoria->id_categoria,
            'sku' => 'JUG-AUT-001',
            'nombre' => 'Pista de Autos Doble Loop',
            'precio' => 100.00,
            'stock_actual' => 10,
            'stock_minimo' => 2,
            'estado' => 'disponible',
        ]);

        $cliente = Cliente::create([
            'dni' => '12345678',
            'nombres' => 'Juan Pérez',
        ]);

        $payload = [
            'id_cliente' => $cliente->id_cliente,
            'tipo_comprobante' => 'boleta',
            'items' => [
                [
                    'id_producto' => $producto->id_producto,
                    'cantidad' => 2,
                ],
            ],
        ];

        $response = $this->actingAs($vendedor)->post('/ventas', $payload);

        $this->assertDatabaseHas('ventas', [
            'id_cliente' => $cliente->id_cliente,
            'id_usuario' => $vendedor->id,
            'estado' => 'pendiente',
            'total' => 200.00,
        ]);

        $venta = Venta::first();
        $response->assertRedirect(route('ventas.show', $venta->id_venta));
    }

    public function test_no_se_puede_vender_mas_cantidad_que_el_stock_disponible(): void
    {
        $vendedor = User::factory()->create([
            'rol' => User::ROL_VENDEDOR,
            'estado' => 'activo',
        ]);

        $categoria = Categoria::create(['nombre' => 'Peluches', 'estado' => 'activo']);

        $producto = Producto::create([
            'id_categoria' => $categoria->id_categoria,
            'sku' => 'JUG-PEL-001',
            'nombre' => 'Oso de Peluche Gigante',
            'precio' => 50.00,
            'stock_actual' => 3,
            'stock_minimo' => 1,
            'estado' => 'disponible',
        ]);

        $cliente = Cliente::create(['dni' => '87654321', 'nombres' => 'María Gomez']);

        $payload = [
            'id_cliente' => $cliente->id_cliente,
            'tipo_comprobante' => 'ticket',
            'items' => [
                [
                    'id_producto' => $producto->id_producto,
                    'cantidad' => 5, // Mayor al stock disponible (3)
                ],
            ],
        ];

        $response = $this->actingAs($vendedor)->post('/ventas', $payload);
        $response->assertSessionHasErrors('stock');
        $this->assertEquals(0, Venta::count());
    }

    public function test_cajero_puede_cobrar_ticket_y_se_descuenta_el_stock_fisico(): void
    {
        $vendedor = User::factory()->create(['rol' => User::ROL_VENDEDOR, 'estado' => 'activo']);
        $cajero = User::factory()->create(['rol' => User::ROL_CAJERO, 'estado' => 'activo']);
        $categoria = Categoria::create(['nombre' => 'Muñecas', 'estado' => 'activo']);

        $producto = Producto::create([
            'id_categoria' => $categoria->id_categoria,
            'sku' => 'JUG-MUN-001',
            'nombre' => 'Muñeca Articulada',
            'precio' => 50.00,
            'stock_actual' => 10,
            'stock_minimo' => 3,
            'estado' => 'disponible',
        ]);

        $cliente = Cliente::create(['dni' => '44556677', 'nombres' => 'Ana Torres']);

        // Crear venta pendiente
        $this->actingAs($vendedor)->post('/ventas', [
            'id_cliente' => $cliente->id_cliente,
            'tipo_comprobante' => 'boleta',
            'items' => [
                ['id_producto' => $producto->id_producto, 'cantidad' => 4],
            ],
        ]);

        $venta = Venta::first();
        $this->assertEquals('pendiente', $venta->estado);

        // Cobro en caja
        $response = $this->actingAs($cajero)->post("/caja/{$venta->id_venta}/cobrar", [
            'metodo_pago' => 'efectivo',
            'monto_recibido' => 250.00, // Total es 200, vuelto 50
        ]);

        $response->assertRedirect(route('caja.comprobante', $venta->id_venta));

        $venta->refresh();
        $this->assertEquals('pagada', $venta->estado);
        $this->assertEquals('efectivo', $venta->metodo_pago);
        $this->assertNotNull($venta->nro_comprob);

        // Verificar que el stock disminuyó de 10 a 6
        $producto->refresh();
        $this->assertEquals(6, $producto->stock_actual);
        $this->assertEquals('disponible', $producto->estado);
    }
    public function test_cada_ticket_recibe_un_codigo_unico_aunque_se_eliminen_ventas(): void
    {
        $vendedor = User::factory()->rol(User::ROL_VENDEDOR)->create();
        $categoria = Categoria::create(['nombre' => 'Didácticos', 'estado' => 'activo']);
        $producto = Producto::create([
            'id_categoria' => $categoria->id_categoria,
            'sku' => 'JUG-DID-001',
            'nombre' => 'Rompecabezas 100 piezas',
            'precio' => 30.00,
            'stock_actual' => 50,
            'stock_minimo' => 5,
            'estado' => 'disponible',
        ]);
        $cliente = Cliente::create(['dni' => '11223344', 'nombres' => 'Rosa Quispe']);

        $payload = [
            'id_cliente' => $cliente->id_cliente,
            'tipo_comprobante' => 'boleta',
            'items' => [['id_producto' => $producto->id_producto, 'cantidad' => 1]],
        ];

        $this->actingAs($vendedor)->post('/ventas', $payload);
        $this->actingAs($vendedor)->post('/ventas', $payload);

        // Con el calculo anterior (count() + 1) la tercera venta repetia el codigo de la segunda
        Venta::first()->delete();
        $this->actingAs($vendedor)->post('/ventas', $payload)->assertSessionHasNoErrors();

        $codigos = Venta::pluck('codigo_venta');
        $this->assertCount(2, $codigos);
        $this->assertCount(2, $codigos->unique());
        $codigos->each(fn ($codigo) => $this->assertMatchesRegularExpression('/^TK-\d{8}-\d{6}$/', $codigo));
    }
}
