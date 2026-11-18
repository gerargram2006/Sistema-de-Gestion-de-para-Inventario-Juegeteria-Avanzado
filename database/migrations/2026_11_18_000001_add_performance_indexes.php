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
        Schema::table('productos', function (Blueprint $table) {
            $table->index('nombre');
            $table->index('estado');
            $table->index(['stock_actual', 'stock_minimo']);
        });

        Schema::table('ventas', function (Blueprint $table) {
            $table->index('estado');
            $table->index('fecha');
        });

        Schema::table('compras', function (Blueprint $table) {
            $table->index('estado');
            $table->index('fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropIndex(['nombre']);
            $table->dropIndex(['estado']);
            $table->dropIndex(['stock_actual', 'stock_minimo']);
        });

        Schema::table('ventas', function (Blueprint $table) {
            $table->dropIndex(['estado']);
            $table->dropIndex(['fecha']);
        });

        Schema::table('compras', function (Blueprint $table) {
            $table->dropIndex(['estado']);
            $table->dropIndex(['fecha']);
        });
    }
};
