<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Devolucion o cambio de un juguete vendido (CUN-04 / CUS-06).
 */
class Devolucion extends Model
{
    use HasFactory;

    public const TIPO_DEVOLUCION = 'devolucion';
    public const TIPO_CAMBIO = 'cambio';

    public const ESTADO_REEMBOLSADA = 'reembolso realizado';
    public const ESTADO_CAMBIADA = 'cambio realizado';
    public const ESTADO_RECHAZADA = 'rechazada';

    /**
     * Plazo maximo en dias, desde la venta, para aceptar una devolucion (RF-19).
     */
    public const PLAZO_DIAS = 7;

    protected $table = 'devoluciones';
    protected $primaryKey = 'id_devolucion';

    protected $fillable = [
        'id_venta',
        'id_producto',
        'id_usuario',
        'tipo',
        'cantidad',
        'motivo',
        'fecha',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
            'fecha' => 'datetime',
        ];
    }

    public function fueAprobada(): bool
    {
        return $this->estado !== self::ESTADO_RECHAZADA;
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'id_venta', 'id_venta');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }
}
