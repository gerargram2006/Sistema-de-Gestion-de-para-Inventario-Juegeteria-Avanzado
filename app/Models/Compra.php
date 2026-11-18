<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

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

    /**
     * El numero de orden (OC-AAAAMMDD-000045) se arma con el id asignado por la
     * base de datos para evitar duplicados entre usuarios concurrentes.
     */
    protected static function booted(): void
    {
        static::creating(function (Compra $compra) {
            $compra->nro_orden ??= 'TMP-' . Str::ulid();
        });

        static::created(function (Compra $compra) {
            if (str_starts_with($compra->nro_orden, 'TMP-')) {
                $compra->nro_orden = sprintf('OC-%s-%06d', ($compra->fecha ?? now())->format('Ymd'), $compra->id_compra);
                $compra->saveQuietly();
            }
        });
    }

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
