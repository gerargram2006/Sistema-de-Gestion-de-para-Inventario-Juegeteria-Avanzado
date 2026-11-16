<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';
    protected $primaryKey = 'id_producto';

    protected $fillable = [
        'id_categoria',
        'sku',
        'nombre',
        'precio',
        'stock_actual',
        'stock_minimo',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'stock_actual' => 'integer',
            'stock_minimo' => 'integer',
        ];
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }

    public function detalleVentas(): HasMany
    {
        return $this->hasMany(DetalleVenta::class, 'id_producto', 'id_producto');
    }

    public function tieneStockCritico(): bool
    {
        return $this->stock_actual <= $this->stock_minimo;
    }

    public function tieneStockSuficiente(int $cantidad): bool
    {
        return $this->stock_actual >= $cantidad;
    }

    public function descontarStock(int $cantidad): bool
    {
        if (!$this->tieneStockSuficiente($cantidad)) {
            return false;
        }

        $this->stock_actual = max(0, $this->stock_actual - $cantidad);
        $this->actualizarEstadoStock();
        return $this->save();
    }

    public function incrementarStock(int $cantidad): bool
    {
        $this->stock_actual += $cantidad;
        $this->actualizarEstadoStock();
        return $this->save();
    }

    public function actualizarEstadoStock(): void
    {
        if ($this->stock_actual <= 0) {
            $this->estado = 'agotado';
        } elseif ($this->stock_actual <= $this->stock_minimo) {
            $this->estado = 'stock bajo';
        } else {
            $this->estado = 'disponible';
        }
    }

    public function scopeStockCritico(Builder $query): Builder
    {
        return $query->whereColumn('stock_actual', '<=', 'stock_minimo');
    }
}
