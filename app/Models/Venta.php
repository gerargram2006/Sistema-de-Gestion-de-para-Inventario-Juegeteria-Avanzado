<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venta extends Model
{
    use HasFactory;

    protected $table = 'ventas';
    protected $primaryKey = 'id_venta';

    protected $fillable = [
        'codigo_venta',
        'id_cliente',
        'id_usuario',
        'fecha',
        'total',
        'metodo_pago',
        'tipo_comprob',
        'nro_comprob',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'datetime',
            'total' => 'decimal:2',
        ];
    }

    public function getNumeroTicketAttribute(): string
    {
        return $this->attributes['codigo_venta'] ?? '';
    }

    public function setNumeroTicketAttribute($value): void
    {
        $this->attributes['codigo_venta'] = $value;
    }

    public function getTipoComprobanteAttribute(): string
    {
        return $this->attributes['tipo_comprob'] ?? 'boleta';
    }

    public function setTipoComprobanteAttribute($value): void
    {
        $this->attributes['tipo_comprob'] = $value;
    }

    public function getNumeroComprobanteAttribute(): ?string
    {
        return $this->attributes['nro_comprob'] ?? null;
    }

    public function setNumeroComprobanteAttribute($value): void
    {
        $this->attributes['nro_comprob'] = $value;
    }

    public function getSubtotalAttribute(): float
    {
        return round((float)$this->total / 1.18, 2);
    }

    public function getIgvAttribute(): float
    {
        return round((float)$this->total - $this->subtotal, 2);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleVenta::class, 'id_venta', 'id_venta');
    }
}
