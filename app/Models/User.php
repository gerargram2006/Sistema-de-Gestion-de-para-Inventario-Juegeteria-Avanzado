<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

#[Fillable(['name', 'usuario', 'email', 'telefono', 'rol', 'estado', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROL_ADMINISTRADOR = 'administrador';
    public const ROL_VENDEDOR = 'vendedor';
    public const ROL_CAJERO = 'cajero';
    public const ROL_ALMACENERO = 'almacenero';

    public function esAdministrador(): bool
    {
        return $this->rol === self::ROL_ADMINISTRADOR;
    }

    public function esVendedor(): bool
    {
        return $this->rol === self::ROL_VENDEDOR;
    }

    public function esCajero(): bool
    {
        return $this->rol === self::ROL_CAJERO;
    }

    public function esAlmacenero(): bool
    {
        return $this->rol === self::ROL_ALMACENERO;
    }

    public const ESTADO_ACTIVO = 'activo';
    public const ESTADO_INACTIVO = 'inactivo';

    public function estaActivo(): bool
    {
        return $this->estado === self::ESTADO_ACTIVO;
    }

    public function tieneRol(string ...$roles): bool
    {
        return in_array($this->rol, $roles, true);
    }

    /**
     * Cierra las sesiones que esta cuenta tenga abiertas en otros equipos.
     *
     * Se usa al cambiar o restablecer la contrasena: si otra persona conocia
     * la clave anterior, su sesion deja de ser valida de inmediato en lugar
     * de seguir abierta hasta que venza.
     *
     * @param  string|null  $exceptoSesion  Sesion que se conserva (la del equipo donde se hizo el cambio).
     * @return int Cantidad de sesiones cerradas.
     */
    public function cerrarSesiones(?string $exceptoSesion = null): int
    {
        $tabla = config('session.table', 'sessions');

        if (config('session.driver') !== 'database' || ! Schema::hasTable($tabla)) {
            return 0;
        }

        return DB::table($tabla)
            ->where('user_id', $this->id)
            ->when($exceptoSesion, fn ($consulta) => $consulta->where('id', '!=', $exceptoSesion))
            ->delete();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
