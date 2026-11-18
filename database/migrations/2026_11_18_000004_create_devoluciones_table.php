<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla 9 del modelo de datos: devoluciones y cambios de juguetes vendidos
 * (CUN-04 / CUS-06), segun el diccionario de datos del informe.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devoluciones', function (Blueprint $table) {
            $table->id('id_devolucion');
            $table->foreignId('id_venta')->constrained('ventas', 'id_venta')->restrictOnDelete();
            $table->foreignId('id_producto')->constrained('productos', 'id_producto')->restrictOnDelete();
            $table->foreignId('id_usuario')->constrained('users', 'id')->restrictOnDelete();
            $table->string('tipo', 20); // devolucion, cambio
            $table->integer('cantidad');
            $table->string('motivo', 255)->nullable();
            $table->dateTime('fecha');
            $table->string('estado', 20); // reembolso realizado, cambio realizado, rechazada
            $table->timestamps();

            $table->index(['id_venta', 'id_producto'], 'devoluciones_venta_producto_index');
            $table->index(['estado', 'fecha'], 'devoluciones_estado_fecha_index');
        });

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE devoluciones ADD CONSTRAINT chk_devoluciones_cantidad CHECK (cantidad > 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('devoluciones');
    }
};
