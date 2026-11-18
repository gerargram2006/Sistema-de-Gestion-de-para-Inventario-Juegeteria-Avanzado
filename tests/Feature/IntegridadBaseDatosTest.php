<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
