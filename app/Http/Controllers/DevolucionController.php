<?php

namespace App\Http\Controllers;

use App\Models\Devolucion;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * CUS-06: Gestionar devoluciones y cambios (CUN-04).
 *
 * El cliente se presenta con el juguete y su comprobante; se busca la venta,
 * se verifica el plazo (RF-19) y se resuelve con reembolso, cambio por otra
 * unidad del mismo juguete o rechazo. El inventario se ajusta segun la
 * solucion aplicada.
 */
class DevolucionController extends Controller
{
    public function index(Request $request): View
    {
        $buscar = trim((string) $request->get('buscar'));
        $venta = null;

        if ($buscar !== '') {
            $venta = Venta::with(['cliente', 'detalles.producto', 'devoluciones'])
                ->where('codigo_venta', $buscar)
                ->orWhere('nro_comprob', $buscar)
                ->first();
        }

        $recientes = Devolucion::with(['venta', 'producto', 'usuario'])
            ->latest('fecha')
            ->take(10)
            ->get();

        return view('devoluciones.index', [
            'buscar' => $buscar,
            'venta' => $venta,
            'recientes' => $recientes,
            'plazoDias' => Devolucion::PLAZO_DIAS,
        ]);
    }

    public function store(Request $request, Venta $venta): RedirectResponse
    {
        $validated = $request->validate([
            'id_producto' => ['required', 'integer'],
            'cantidad' => ['required', 'integer', 'min:1', 'max:10000'],
            'tipo' => ['required', Rule::in([Devolucion::TIPO_DEVOLUCION, Devolucion::TIPO_CAMBIO])],
            'decision' => ['required', Rule::in(['aprobar', 'rechazar'])],
            'motivo' => ['nullable', 'string', 'max:255', 'required_if:decision,rechazar'],
        ], [
            'motivo.required_if' => 'Indique el motivo del rechazo (por ejemplo, daños por mal uso).',
        ]);

        $aprobar = $validated['decision'] === 'aprobar';

        try {
            $devolucion = DB::transaction(function () use ($venta, $validated, $aprobar, $request) {
                $venta = Venta::whereKey($venta->id_venta)->lockForUpdate()->firstOrFail();

                if ($venta->estado !== 'pagada') {
                    throw new \DomainException("La venta {$venta->codigo_venta} está en estado '{$venta->estado}': solo se atienden devoluciones de ventas pagadas.");
                }

                if ($aprobar && $venta->fecha->lt(now()->subDays(Devolucion::PLAZO_DIAS))) {
                    throw new \DomainException('La venta superó el plazo de ' . Devolucion::PLAZO_DIAS . ' días para devoluciones y cambios (RF-19).');
                }

                $detalle = $venta->detalles()->where('id_producto', $validated['id_producto'])->first();

                if (! $detalle) {
                    throw new \DomainException('El juguete indicado no forma parte de esta venta.');
                }

                $disponible = $detalle->cantidad - $venta->cantidadDevuelta($detalle->id_producto);

                if ($validated['cantidad'] > $disponible) {
                    throw new \DomainException("Solo quedan {$disponible} unidad(es) de este juguete por devolver en la venta.");
                }

                $estado = Devolucion::ESTADO_RECHAZADA;

                if ($aprobar) {
                    $producto = Producto::lockForUpdate()->findOrFail($detalle->id_producto);

                    if ($validated['tipo'] === Devolucion::TIPO_DEVOLUCION) {
                        // El juguete vuelve al inventario y se reembolsa el dinero
                        $producto->incrementarStock($validated['cantidad']);
                        $estado = Devolucion::ESTADO_REEMBOLSADA;
                    } else {
                        // Cambio por otra unidad del mismo juguete: sale una unidad nueva del stock
                        if (! $producto->descontarStock($validated['cantidad'])) {
                            throw new \DomainException("No hay stock de '{$producto->nombre}' para entregar el reemplazo. Disponible: {$producto->stock_actual} un.");
                        }
                        $estado = Devolucion::ESTADO_CAMBIADA;
                    }
                }

                $devolucion = Devolucion::create([
                    'id_venta' => $venta->id_venta,
                    'id_producto' => $detalle->id_producto,
                    'id_usuario' => $request->user()->id,
                    'tipo' => $validated['tipo'],
                    'cantidad' => $validated['cantidad'],
                    'motivo' => $validated['motivo'] ?? null,
                    'fecha' => now(),
                    'estado' => $estado,
                ]);

                // Si se reembolsaron todas las unidades vendidas, la venta queda devuelta
                $unidadesVendidas = (int) $venta->detalles()->sum('cantidad');
                $unidadesReembolsadas = (int) $venta->devoluciones()
                    ->where('estado', Devolucion::ESTADO_REEMBOLSADA)
                    ->sum('cantidad');

                if ($unidadesReembolsadas >= $unidadesVendidas) {
                    $venta->update(['estado' => 'devuelta']);
                }

                return $devolucion;
            });
        } catch (\DomainException $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->withErrors(['error' => 'No se pudo registrar la devolución. Intente nuevamente o comuníquese con soporte.']);
        }

        $mensaje = match ($devolucion->estado) {
            Devolucion::ESTADO_REEMBOLSADA => 'Devolución registrada. Reembolsar al cliente S/ '
                . number_format($devolucion->cantidad * (float) $venta->detalles()->where('id_producto', $devolucion->id_producto)->value('precio_unit'), 2)
                . '. El juguete regresó al inventario.',
            Devolucion::ESTADO_CAMBIADA => 'Cambio registrado. Entregue al cliente ' . $devolucion->cantidad . ' unidad(es) nueva(s); el stock fue actualizado.',
            default => 'Solicitud rechazada y registrada con su motivo. El inventario no se modificó.',
        };

        return redirect()->route('devoluciones.index', ['buscar' => $venta->codigo_venta])
            ->with('success', $mensaje);
    }
}
