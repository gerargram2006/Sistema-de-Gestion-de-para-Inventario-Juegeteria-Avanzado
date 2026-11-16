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
        Schema::table('users', function (Blueprint $table) {
            $table->string('rol', 30)->default('vendedor')->after('password'); // administrador, vendedor, cajero, almacenero
            $table->string('usuario', 50)->nullable()->unique()->after('name');
            $table->string('telefono', 15)->nullable()->after('email');
            $table->string('estado', 20)->default('activo')->after('rol');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['rol', 'usuario', 'telefono', 'estado']);
        });
    }
};
