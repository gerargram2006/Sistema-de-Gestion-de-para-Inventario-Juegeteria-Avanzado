<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indices para las consultas mas frecuentes del sistema. Sin ellos, MySQL
 * recorria la tabla completa y ordenaba en memoria (EXPLAIN: type ALL,
 * Using filesort), lo que degrada el tiempo de respuesta (RNF-01) a medida
 * que crece el historial.
 *
 * Complementa a add_performance_indexes (indices simples sobre fecha, estado
 * y nombre): aqui se agregan los compuestos y los que faltaban.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            // Bandeja de caja, dashboard e historial: WHERE estado ... ORDER BY fecha
            $table->index(['estado', 'fecha'], 'ventas_estado_fecha_index');
            // Siguiente correlativo de la serie: WHERE nro_comprob LIKE 'B001-%'
            $table->index('nro_comprob', 'ventas_nro_comprob_index');
        });

        Schema::table('compras', function (Blueprint $table) {
            // Ordenes por recibir: WHERE estado IN (...) ORDER BY fecha
            $table->index(['estado', 'fecha'], 'compras_estado_fecha_index');
        });

        Schema::table('productos', function (Blueprint $table) {
            // Punto de venta y catalogo: WHERE estado ... ORDER BY nombre
            $table->index(['estado', 'nombre'], 'productos_estado_nombre_index');
        });

        Schema::table('clientes', function (Blueprint $table) {
            // Lista de clientes del POS ordenada por nombre
            $table->index('nombres', 'clientes_nombres_index');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropIndex('ventas_estado_fecha_index');
            $table->dropIndex('ventas_nro_comprob_index');
        });

        Schema::table('compras', function (Blueprint $table) {
            $table->dropIndex('compras_estado_fecha_index');
        });

        Schema::table('productos', function (Blueprint $table) {
            $table->dropIndex('productos_estado_nombre_index');
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->dropIndex('clientes_nombres_index');
        });
    }
};
