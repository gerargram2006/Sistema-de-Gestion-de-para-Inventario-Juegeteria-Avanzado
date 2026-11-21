<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ComprobanteContador extends Model
{
    use HasFactory;

    protected $table = 'comprobante_contadores';
    protected $primaryKey = 'id';

    protected $fillable = [
        'serie',
        'ultimo_correlativo',
    ];

    protected function casts(): array
    {
        return [
            'ultimo_correlativo' => 'integer',
        ];
    }

    /**
     * Obtiene o crea el contador para una serie y devuelve el siguiente correlativo
     * de forma atómica, bloqueando la fila para evitar duplicados bajo concurrencia.
     */
    public static function siguienteCorrelativo(string $serie): int
    {
        return DB::transaction(function () use ($serie) {
            $contador = static::where('serie', $serie)->lockForUpdate()->first();

            if (! $contador) {
                $contador = static::create([
                    'serie' => $serie,
                    'ultimo_correlativo' => 1,
                ]);

                return 1;
            }

            $contador->increment('ultimo_correlativo');

            return (int) $contador->fresh()->ultimo_correlativo;
        });
    }
}
