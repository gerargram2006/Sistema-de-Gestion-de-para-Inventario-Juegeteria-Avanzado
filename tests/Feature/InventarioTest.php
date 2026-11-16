<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventarioTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalogo_muestra_juguetes_y_permite_filtrar(): void
    {
        $admin = User::factory()->create([
            'rol' => User::ROL_ADMINISTRADOR,
            'estado' => 'activo',
        ]);

        $categoria = Categoria::create([
            'nombre' => 'Juegos de Mesa',
            'descripcion' => 'Juegos de mesa familiares',
            'estado' => 'activo',
        ]);

        $producto1 = Producto::create([
            'id_categoria' => $categoria->id_categoria,
            'sku' => 'JUG-TEST-001',
            'nombre' => 'Monopoly Clásico',
            'precio' => 89.90,
            'stock_actual' => 15,
            'stock_minimo' => 5,
            'estado' => 'disponible',
        ]);

        $producto2 = Producto::create([
            'id_categoria' => $categoria->id_categoria,
            'sku' => 'JUG-TEST-002',
            'nombre' => 'Jenga Gigante',
            'precio' => 45.00,
            'stock_actual' => 2,
            'stock_minimo' => 5,
            'estado' => 'stock bajo',
        ]);

        $response = $this->actingAs($admin)->get('/inventario');
        $response->assertOk();
        $response->assertSee('Monopoly Clásico');
        $response->assertSee('Jenga Gigante');

        // Filtrar por stock crítico
        $responseCritico = $this->actingAs($admin)->get('/inventario?filtro_stock=critico');
        $responseCritico->assertOk();
        $responseCritico->assertSee('Jenga Gigante');
        $responseCritico->assertDontSee('Monopoly Clásico');
    }

    public function test_administrador_puede_crear_nuevo_juguete(): void
    {
        $admin = User::factory()->create([
            'rol' => User::ROL_ADMINISTRADOR,
            'estado' => 'activo',
        ]);

        $categoria = Categoria::create([
            'nombre' => 'Construcción',
            'descripcion' => 'Bloques para armar',
            'estado' => 'activo',
        ]);

        $response = $this->actingAs($admin)->post('/inventario', [
            'id_categoria' => $categoria->id_categoria,
            'sku' => 'JUG-NEW-001',
            'nombre' => 'Set de Bloques 500 pcs',
            'precio' => 120.00,
            'stock_actual' => 20,
            'stock_minimo' => 5,
        ]);

        $response->assertRedirect('/inventario');
        $this->assertDatabaseHas('productos', [
            'sku' => 'JUG-NEW-001',
            'nombre' => 'Set de Bloques 500 pcs',
            'stock_actual' => 20,
            'estado' => 'disponible',
        ]);
    }
}
