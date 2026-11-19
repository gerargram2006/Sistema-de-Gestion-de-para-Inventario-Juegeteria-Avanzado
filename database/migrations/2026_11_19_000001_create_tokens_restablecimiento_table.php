<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tokens de un solo uso para restablecer la contrasena de un trabajador.
 *
 * La tienda no cuenta con servidor de correo, por lo que el administrador
 * entrega el codigo en persona. Del token solo se guarda su hash: si alguien
 * leyera la tabla no podria reconstruir el codigo original.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tokens_restablecimiento', function (Blueprint $table) {
            $table->id('id_token');

            // Dueno de la cuenta que se va a restablecer
            $table->foreignId('id_usuario')->constrained('users')->cascadeOnDelete();

            // Administrador que genero el token (queda la trazabilidad)
            $table->foreignId('id_admin')->constrained('users')->restrictOnDelete();

            $table->string('token_hash');
            $table->timestamp('expira_en');
            $table->timestamp('usado_en')->nullable();
            $table->timestamps();

            // Busqueda de los tokens vigentes de una cuenta al canjear el codigo
            $table->index(['id_usuario', 'usado_en'], 'tokens_usuario_usado_index');
            $table->index('expira_en', 'tokens_expira_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tokens_restablecimiento');
    }
};
