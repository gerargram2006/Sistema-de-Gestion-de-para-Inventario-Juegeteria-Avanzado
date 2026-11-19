<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Token de un solo uso para restablecer la contrasena de un trabajador.
 *
 * Reglas de seguridad aplicadas:
 *  - En la base de datos solo se guarda el hash del codigo, nunca el codigo.
 *  - Cada token vence a los PlazoMinutos y sirve una sola vez (usado_en).
 *  - Generar un token nuevo anula los anteriores de esa misma cuenta.
 */
class TokenRestablecimiento extends Model
{
    protected $table = 'tokens_restablecimiento';

    protected $primaryKey = 'id_token';

    protected $fillable = ['id_usuario', 'id_admin', 'token_hash', 'expira_en', 'usado_en'];

    /** Minutos de vigencia del codigo entregado al trabajador. */
    public const VIGENCIA_MINUTOS = 30;

    /** Cantidad de caracteres del codigo (32^12 combinaciones posibles). */
    public const LONGITUD = 12;

    /** Alfabeto sin caracteres ambiguos (sin O/0 ni I/1/L) para dictarlo sin errores. */
    private const ALFABETO = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    protected function casts(): array
    {
        return [
            'expira_en' => 'datetime',
            'usado_en' => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function administrador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_admin');
    }

    /**
     * Genera un token para el usuario indicado, anula los anteriores y
     * devuelve el codigo en claro. Es la unica vez que el codigo existe
     * fuera del navegador del administrador.
     */
    public static function generarPara(User $usuario, User $administrador): string
    {
        $codigo = '';

        for ($i = 0; $i < self::LONGITUD; $i++) {
            $codigo .= self::ALFABETO[random_int(0, strlen(self::ALFABETO) - 1)];
        }

        DB::transaction(function () use ($usuario, $administrador, $codigo) {
            // Un token nuevo deja sin efecto los anteriores de la misma cuenta
            self::where('id_usuario', $usuario->id)->whereNull('usado_en')->update(['usado_en' => now()]);

            self::create([
                'id_usuario' => $usuario->id,
                'id_admin' => $administrador->id,
                'token_hash' => Hash::make($codigo),
                'expira_en' => now()->addMinutes(self::VIGENCIA_MINUTOS),
            ]);
        });

        return $codigo;
    }

    /**
     * Busca el token vigente que corresponda al codigo escrito por el usuario.
     * Devuelve null si no hay ninguno (codigo erroneo, ya usado o vencido).
     */
    public static function buscarVigente(User $usuario, string $codigo): ?self
    {
        return self::where('id_usuario', $usuario->id)
            ->vigentes()
            ->get()
            ->first(fn (self $token) => Hash::check(self::normalizar($codigo), $token->token_hash));
    }

    /** Tokens que aun no se han usado ni vencido. */
    public function scopeVigentes(Builder $query): Builder
    {
        return $query->whereNull('usado_en')->where('expira_en', '>', now());
    }

    /** Marca el token como consumido: no puede volver a usarse. */
    public function marcarUsado(): void
    {
        $this->update(['usado_en' => now()]);
    }

    /** Quita espacios y guiones y pasa a mayusculas lo que escribio el usuario. */
    public static function normalizar(string $codigo): string
    {
        return Str::upper(preg_replace('/[^A-Za-z0-9]/', '', $codigo));
    }

    /** Presenta el codigo en grupos de cuatro para leerlo o dictarlo: ABCD-EFGH-JKMN. */
    public static function formatear(string $codigo): string
    {
        return implode('-', str_split(self::normalizar($codigo), 4));
    }
}
