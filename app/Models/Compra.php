<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Compra extends Model
{
    use HasFactory;

    protected $table = 'compras';
    protected $primaryKey = 'id_compra';

    protected $fillable = [
        'nro_orden',
        'id_usuario',
        'proveedor',
        'fecha',
        'total',
        'estado',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'datetime',
            'total' => 'decimal:2',
        ];
    }

    public function getNumeroOrdenAttribute(): string
    {
        return $this->attributes['nro_orden'] ?? '';
    }

    public function setNumeroOrdenAttribute($value): void
    {
        $this->attributes['nro_orden'] = $value;
    }

    public function getFechaRecepcionAttribute(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleCompra::class, 'id_compra', 'id_compra');
    }
}
