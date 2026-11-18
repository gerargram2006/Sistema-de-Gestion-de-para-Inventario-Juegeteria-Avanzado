<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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
