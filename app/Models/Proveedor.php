<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proveedor extends Model
{
    use HasFactory;

    // Laravel pluralizaría mal ("proveedors"), por eso se indica la tabla
    protected $table = 'proveedores';

    protected $primaryKey = 'id_proveedor';

    protected $fillable = [
        'razon_social',
        'ruc',
        'telefono',
        'email',
        'direccion',
        'activo',
    ];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function compras(): HasMany
    {
        return $this->hasMany(Compra::class, 'id_proveedor', 'id_proveedor');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }
}
