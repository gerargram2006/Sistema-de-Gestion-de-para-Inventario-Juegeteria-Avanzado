<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compras', function (Blueprint $table) {
            $table->string('guia_remision', 50)->nullable()->after('observaciones');
            $table->string('observaciones_recepcion', 255)->nullable()->after('guia_remision');
            $table->dateTime('fecha_recepcion')->nullable()->after('observaciones_recepcion');
        });
    }

    public function down(): void
    {
        Schema::table('compras', function (Blueprint $table) {
            $table->dropColumn(['guia_remision', 'observaciones_recepcion', 'fecha_recepcion']);
        });
    }
};
