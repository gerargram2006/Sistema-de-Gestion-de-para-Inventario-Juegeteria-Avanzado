<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Kardex de movimientos de inventario (CUS-05 / CUN-03 / RF-13 a RF-16).
 */
class MovimientoInventario extends Model
{
    use HasFactory;

    public const TIPO_INVENTARIO_INICIAL = 'inventario_inicial';

    public const TIPO_ENTRADA_COMPRA = 'entrada_compra';

    public const TIPO_SALIDA_VENTA = 'salida_venta';

    public const TIPO_ENTRADA_DEVOLUCION = 'entrada_devolucion';

    public const TIPO_SALIDA_CAMBIO = 'salida_cambio';

    public const TIPO_MERMA = 'merma';

    public const TIPO_AJUSTE_POSITIVO = 'ajuste_positivo';

    public const TIPO_AJUSTE_NEGATIVO = 'ajuste_negativo';

    public const ETIQUETAS_TIPO = [
        self::TIPO_INVENTARIO_INICIAL => 'Inventario inicial',
        self::TIPO_ENTRADA_COMPRA => 'Ingreso por compra',
        self::TIPO_SALIDA_VENTA => 'Salida por venta',
        self::TIPO_ENTRADA_DEVOLUCION => 'Reingreso por devolución',
        self::TIPO_SALIDA_CAMBIO => 'Salida por cambio',
        self::TIPO_MERMA => 'Merma / rotura',
        self::TIPO_AJUSTE_POSITIVO => 'Ajuste positivo (auditoría)',
        self::TIPO_AJUSTE_NEGATIVO => 'Ajuste negativo (auditoría)',
    ];

    public const TIPOS_ENTRADA = [
        self::TIPO_INVENTARIO_INICIAL,
        self::TIPO_ENTRADA_COMPRA,
        self::TIPO_ENTRADA_DEVOLUCION,
        self::TIPO_AJUSTE_POSITIVO,
    ];

    protected $table = 'movimientos_inventario';

    protected $primaryKey = 'id_movimiento';

    protected $fillable = [
        'id_producto',
        'id_usuario',
        'tipo',
        'cantidad',
        'stock_anterior',
        'stock_nuevo',
        'referencia',
        'motivo',
        'fecha',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
            'stock_anterior' => 'integer',
            'stock_nuevo' => 'integer',
            'fecha' => 'datetime',
        ];
    }

    public static function registrar(
        Producto $producto,
        string $tipo,
        int $cantidad,
        int $stockAnterior,
        int $stockNuevo,
        ?string $referencia = null,
        ?string $motivo = null,
        ?int $idUsuario = null,
    ): self {
        return self::create([
            'id_producto' => $producto->id_producto,
            'id_usuario' => $idUsuario ?? Auth::id(),
            'tipo' => $tipo,
            'cantidad' => $cantidad,
            'stock_anterior' => $stockAnterior,
            'stock_nuevo' => $stockNuevo,
            'referencia' => $referencia,
            'motivo' => $motivo,
            'fecha' => now(),
        ]);
    }

    public function esEntrada(): bool
    {
        return in_array($this->tipo, self::TIPOS_ENTRADA, true);
    }

    public function etiquetaTipo(): string
    {
        return self::ETIQUETAS_TIPO[$this->tipo] ?? ucfirst(str_replace('_', ' ', $this->tipo));
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
