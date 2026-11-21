<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Kardex de movimientos de inventario (CUS-05 / CUN-03 / RF-13 a RF-16).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id('id_movimiento');
            $table->foreignId('id_producto')->constrained('productos', 'id_producto')->restrictOnDelete();
            $table->foreignId('id_usuario')->nullable()->constrained('users', 'id')->restrictOnDelete();
            $table->string('tipo', 30);
            $table->integer('cantidad');
            $table->integer('stock_anterior');
            $table->integer('stock_nuevo');
            $table->string('referencia', 50)->nullable();
            $table->string('motivo', 255)->nullable();
            $table->dateTime('fecha');
            $table->timestamps();

            $table->index(['id_producto', 'fecha'], 'mov_inv_producto_fecha_index');
            $table->index(['tipo', 'fecha'], 'mov_inv_tipo_fecha_index');
        });

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE movimientos_inventario ADD CONSTRAINT chk_mov_inv_cantidad CHECK (cantidad > 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
    }
};
