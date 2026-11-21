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

    public function devoluciones(): HasMany
    {
        return $this->hasMany(Devolucion::class, 'id_producto', 'id_producto');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'id_producto', 'id_producto');
    }

    public function tieneStockCritico(): bool
    {
        return ! $this->estaInactivo() && $this->stock_actual <= $this->stock_minimo;
    }

    /**
     * Cache en memoria de las imagenes existentes en public/images/productos/
     * para evitar multiples llamadas file_exists() por cada producto en listados.
     *
     * @var array<string, string>|null
     */
    protected static ?array $mapaImagenes = null;

    /**
     * Ruta (relativa a public/) de la foto del juguete, si fue cargada en
     * public/images/productos/{SKU}.jpg|png|webp. Null si aun no tiene foto.
     */
    public function rutaImagen(): ?string
    {
        if (self::$mapaImagenes === null) {
            self::$mapaImagenes = [];
            $directorio = public_path('images/productos');

            if (is_dir($directorio)) {
                foreach (scandir($directorio) ?: [] as $archivo) {
                    $ext = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'png', 'webp'], true)) {
                        $sku = pathinfo($archivo, PATHINFO_FILENAME);
                        self::$mapaImagenes[$sku] ??= "images/productos/{$archivo}";
                    }
                }
            }
        }

        return self::$mapaImagenes[$this->sku] ?? null;
    }

    /**
     * Juguete dado de baja del catalogo: no se vende ni se sugiere reponerlo,
     * pero se conserva por su historial de ventas y compras.
     */
    public function estaInactivo(): bool
    {
        return $this->estado === 'inactivo';
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

    /**
     * Recalcula el estado segun el stock: agotado, stock bajo o disponible.
     * Un juguete dado de baja (inactivo) conserva su estado aunque cambie
     * su stock por una venta, devolucion o recepcion.
     */
    public function actualizarEstadoStock(): void
    {
        if ($this->estaInactivo()) {
            return;
        }

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
        return $query->whereColumn('stock_actual', '<=', 'stock_minimo')
            ->where('estado', '!=', 'inactivo');
    }
}
