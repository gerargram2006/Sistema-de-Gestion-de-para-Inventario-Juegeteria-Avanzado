<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Reglas de integridad que garantiza el propio motor de base de datos,
 * independientemente de las validaciones de la aplicacion.
 */
class IntegridadBaseDatosTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_se_puede_eliminar_una_categoria_que_tiene_juguetes(): void
    {
        $categoria = Categoria::create(['nombre' => 'Juegos de Mesa', 'estado' => 'activo']);
        Producto::create([
            'id_categoria' => $categoria->id_categoria,
            'sku' => 'JUG-MES-001',
            'nombre' => 'Ludo Familiar',
            'precio' => 25.00,
            'stock_actual' => 10,
            'stock_minimo' => 2,
            'estado' => 'disponible',
        ]);

        try {
            $categoria->delete();
            $this->fail('La base de datos permitio eliminar una categoria con productos.');
        } catch (QueryException) {
            // Esperado: ON DELETE RESTRICT
        }

        $this->assertDatabaseHas('productos', ['sku' => 'JUG-MES-001']);
    }
    public function test_mysql_rechaza_stock_negativo_y_precios_invalidos(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            $this->markTestSkipped('Las restricciones CHECK solo se crean en MySQL/MariaDB.');
        }

        $categoria = Categoria::create(['nombre' => 'Peluches', 'estado' => 'activo']);
        $producto = Producto::create([
            'id_categoria' => $categoria->id_categoria,
            'sku' => 'JUG-PEL-077',
            'nombre' => 'Conejo de Peluche',
            'precio' => 30.00,
            'stock_actual' => 2,
            'stock_minimo' => 1,
            'estado' => 'disponible',
        ]);

        foreach (['stock_actual' => -1, 'precio' => 0] as $columna => $valorInvalido) {
            try {
                DB::table('productos')->where('id_producto', $producto->id_producto)->update([$columna => $valorInvalido]);
                $this->fail("MySQL acepto {$columna} = {$valorInvalido}.");
            } catch (QueryException $e) {
                $this->assertStringContainsString('chk_productos', $e->getMessage());
            }
        }

        $this->assertSame(2, $producto->fresh()->stock_actual);
    }
}
