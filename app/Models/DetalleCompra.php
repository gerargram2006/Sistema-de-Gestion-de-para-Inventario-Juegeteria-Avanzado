<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleCompra extends Model
{
    use HasFactory;

    protected $table = 'detalle_compras';
    protected $primaryKey = 'id_detalle_compra';

    protected $fillable = [
        'id_compra',
        'id_producto',
        'cantidad',
        'costo_unit',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
            'costo_unit' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function getPrecioCostoAttribute(): float
    {
        return (float) ($this->attributes['costo_unit'] ?? 0.0);
    }

    public function setPrecioCostoAttribute($value): void
    {
        $this->attributes['costo_unit'] = $value;
    }

    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class, 'id_compra', 'id_compra');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }
}
