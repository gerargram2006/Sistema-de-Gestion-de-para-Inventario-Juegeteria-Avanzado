<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * La razon social del catalogo admite 150 caracteres; la columna
     * desnormalizada de compras debe permitir el mismo tamano.
     */
    public function up(): void
    {
        Schema::table('compras', function (Blueprint $table) {
            $table->string('proveedor', 150)->change();
        });
    }

    public function down(): void
    {
        Schema::table('compras', function (Blueprint $table) {
            $table->string('proveedor', 100)->change();
        });
    }
};
