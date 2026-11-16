<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id('id_venta');
            $table->string('codigo_venta', 30)->unique();
            $table->foreignId('id_cliente')->constrained('clientes', 'id_cliente');
            $table->foreignId('id_usuario')->constrained('users', 'id');
            $table->dateTime('fecha');
            $table->decimal('total', 10, 2)->default(0.00);
            $table->string('metodo_pago', 20)->default('efectivo'); // efectivo, tarjeta, transferencia
            $table->string('tipo_comprob', 10)->default('boleta'); // boleta, factura
            $table->string('nro_comprob', 20)->nullable();
            $table->string('estado', 20)->default('pendiente'); // pendiente, pagada, anulada
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
