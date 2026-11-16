<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleVenta extends Model
{
    use HasFactory;

    protected $table = 'detalle_ventas';
    protected $primaryKey = 'id_detalle';

    protected $fillable = [
        'id_venta',
        'id_producto',
        'cantidad',
        'precio_unit',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
            'precio_unit' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function getPrecioUnitarioAttribute(): float
    {
        return (float) ($this->attributes['precio_unit'] ?? 0.0);
    }

    public function setPrecioUnitarioAttribute($value): void
    {
        $this->attributes['precio_unit'] = $value;
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'id_venta', 'id_venta');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }
}
