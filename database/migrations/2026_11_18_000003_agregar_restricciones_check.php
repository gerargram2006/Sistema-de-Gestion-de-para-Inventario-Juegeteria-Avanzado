<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Restricciones CHECK de dominio: el propio motor rechaza stock negativo,
 * precios o cantidades no positivas y totales negativos, aunque un error
 * de programacion o una consulta manual intente guardarlos.
 *
 * MySQL aplica CHECK desde la version 8.0.16 y MariaDB desde la 10.2.
 * SQLite (usado en las pruebas rapidas) no permite agregarlas a una tabla
 * existente, por lo que ahi se omiten.
 */
return new class extends Migration
{
    private const RESTRICCIONES = [
        'productos' => [
            'chk_productos_stock_actual' => 'stock_actual >= 0',
            'chk_productos_stock_minimo' => 'stock_minimo >= 0',
            'chk_productos_precio' => 'precio > 0',
        ],
        'ventas' => [
            'chk_ventas_total' => 'total >= 0',
        ],
        'detalle_ventas' => [
            'chk_detalle_ventas_cantidad' => 'cantidad > 0',
            'chk_detalle_ventas_precio' => 'precio_unit >= 0',
            'chk_detalle_ventas_subtotal' => 'subtotal >= 0',
        ],
        'compras' => [
            'chk_compras_total' => 'total >= 0',
        ],
        'detalle_compras' => [
            'chk_detalle_compras_cantidad' => 'cantidad > 0',
            'chk_detalle_compras_costo' => 'costo_unit >= 0',
            'chk_detalle_compras_subtotal' => 'subtotal >= 0',
        ],
    ];

    public function up(): void
    {
        if (! $this->soportaCheck()) {
            return;
        }

        foreach (self::RESTRICCIONES as $tabla => $restricciones) {
            foreach ($restricciones as $nombre => $condicion) {
                DB::statement("ALTER TABLE {$tabla} ADD CONSTRAINT {$nombre} CHECK ({$condicion})");
            }
        }
    }

    public function down(): void
    {
        if (! $this->soportaCheck()) {
            return;
        }

        $sentencia = DB::getDriverName() === 'mariadb' ? 'DROP CONSTRAINT' : 'DROP CHECK';

        foreach (self::RESTRICCIONES as $tabla => $restricciones) {
            foreach (array_keys($restricciones) as $nombre) {
                DB::statement("ALTER TABLE {$tabla} {$sentencia} {$nombre}");
            }
        }
    }

    private function soportaCheck(): bool
    {
        return in_array(DB::getDriverName(), ['mysql', 'mariadb'], true);
    }
};
