<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';
    protected $primaryKey = 'id_cliente';

    protected $fillable = [
        'dni',
        'nombres',
        'telefono',
        'correo',
        'estado',
    ];

    /**
     * Alias de acceso y mutación para compatibilidad con $cliente->nombre.
     */
    public function getNombreAttribute(): string
    {
        return $this->attributes['nombres'] ?? '';
    }

    public function setNombreAttribute($value): void
    {
        $this->attributes['nombres'] = $value;
    }

    /**
     * Alias de acceso y mutación para compatibilidad con $cliente->email.
     */
    public function getEmailAttribute(): ?string
    {
        return $this->attributes['correo'] ?? null;
    }

    public function setEmailAttribute($value): void
    {
        $this->attributes['correo'] = $value;
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'id_cliente', 'id_cliente');
    }
}
