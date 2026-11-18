<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La llave foranea productos.id_categoria tenia ON DELETE CASCADE: eliminar
 * una categoria borraba en silencio todos sus juguetes del catalogo (y fallaba
 * a medias si alguno ya figuraba en una venta o compra). Con RESTRICT la base
 * de datos impide eliminar una categoria mientras tenga productos; para
 * retirarla se marca como inactiva.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropForeign(['id_categoria']);

            $table->foreign('id_categoria')
                ->references('id_categoria')
                ->on('categorias')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropForeign(['id_categoria']);

            $table->foreign('id_categoria')
                ->references('id_categoria')
                ->on('categorias')
                ->cascadeOnDelete();
        });
    }
};
