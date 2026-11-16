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
        Schema::create('compras', function (Blueprint $table) {
            $table->id('id_compra');
            $table->string('nro_orden', 30)->unique();
            $table->foreignId('id_usuario')->constrained('users', 'id');
            $table->string('proveedor', 100);
            $table->dateTime('fecha');
            $table->decimal('total', 10, 2)->default(0.00);
            $table->string('estado', 20)->default('generada'); // generada, enviada, recibida, cancelada
            $table->string('observaciones', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};
