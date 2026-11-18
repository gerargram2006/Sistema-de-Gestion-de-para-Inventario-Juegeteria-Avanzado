<?php

namespace Tests\Unit;

use App\Models\Producto;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ProductoEstadoTest extends TestCase
{
    public static function casosDeStock(): array
    {
        return [
            'sin stock' => [0, 5, 'agotado'],
            'igual al minimo' => [5, 5, 'stock bajo'],
            'debajo del minimo' => [3, 5, 'stock bajo'],
            'sobre el minimo' => [6, 5, 'disponible'],
        ];
    }

    #[DataProvider('casosDeStock')]
    public function test_el_estado_se_calcula_segun_el_stock(int $stock, int $minimo, string $esperado): void
    {
        $producto = new Producto(['stock_actual' => $stock, 'stock_minimo' => $minimo, 'estado' => 'disponible']);

        $producto->actualizarEstadoStock();

        $this->assertSame($esperado, $producto->estado);
    }

    public function test_un_juguete_inactivo_conserva_su_estado(): void
    {
        $producto = new Producto(['stock_actual' => 40, 'stock_minimo' => 5, 'estado' => 'inactivo']);

        $producto->actualizarEstadoStock();

        $this->assertSame('inactivo', $producto->estado);
    }
}
