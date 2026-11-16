<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CajaController extends Controller
{
    /**
     * Muestra la bandeja de tickets pendientes por cobrar en caja (CUS-02).
     */
    public function index(Request $request): View
    {
        $buscar = $request->get('buscar');

        $queryPendientes = Venta::with(['cliente', 'usuario', 'detalles.producto'])
            ->where('estado', 'pendiente');

        if ($buscar) {
            $queryPendientes->where(function ($q) use ($buscar) {
                $q->where('codigo_venta', 'like', "%{$buscar}%")
                  ->orWhereHas('cliente', function ($cq) use ($buscar) {
                      $cq->where('nombres', 'like', "%{$buscar}%")
                         ->orWhere('dni', 'like', "%{$buscar}%");
                  });
            });
        }

        $ticketsPendientes = $queryPendientes->latest('fecha')->get();

        $ultimasCobradas = Venta::with(['cliente', 'usuario'])
            ->where('estado', 'pagada')
            ->latest('updated_at')
            ->take(8)
            ->get();

        return view('caja.index', compact('ticketsPendientes', 'ultimasCobradas', 'buscar'));
    }

    /**
     * Procesa el cobro de un ticket, deduce el stock del inventario y emite comprobante.
     */
    public function cobrar(Request $request, Venta $venta): RedirectResponse
    {
        if ($venta->estado !== 'pendiente') {
            return back()->withErrors(['error' => 'El ticket seleccionado ya no se encuentra en estado pendiente.']);
        }

        $validated = $request->validate([
            'metodo_pago' => 'required|in:efectivo,tarjeta,yape,plin',
            'monto_recibido' => 'required|numeric|min:' . $venta->total,
        ], [
            'monto_recibido.min' => 'El monto recibido debe ser igual o superior al total de la venta (S/ ' . number_format($venta->total, 2) . ').',
        ]);

        $montoRecibido = (float) $validated['monto_recibido'];
        $vuelto = round($montoRecibido - (float) $venta->total, 2);

        // Generar número de comprobante oficial (B001 o F001)
        $prefijo = match ($venta->tipo_comprob) {
            'factura' => 'F001-',
            default => 'B001-',
        };
        $numeroComprobante = $prefijo . str_pad((string) ($venta->id_venta), 6, '0', STR_PAD_LEFT);

        DB::beginTransaction();
        try {
            // 1. Deducir stock físico de cada producto y actualizar estado si llega a 0 o stock mínimo
            foreach ($venta->detalles as $detalle) {
                $producto = Producto::lockForUpdate()->findOrFail($detalle->id_producto);

                if (!$producto->descontarStock($detalle->cantidad)) {
                    DB::rollBack();
                    return back()->withErrors([
                        'error' => "Stock insuficiente al momento de cobrar para '{$producto->nombre}'. Disponible: {$producto->stock_actual} un.",
                    ]);
                }
            }

            // 2. Actualizar el estado de la venta a pagada
            $venta->update([
                'estado' => 'pagada',
                'metodo_pago' => $validated['metodo_pago'],
                'nro_comprob' => $numeroComprobante,
            ]);

            DB::commit();

            return redirect()->route('caja.comprobante', $venta->id_venta)
                ->with('success', "Cobro realizado con éxito. Comprobante {$numeroComprobante} emitido. Vuelto a entregar: S/ " . number_format($vuelto, 2));
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al procesar el cobro: ' . $e->getMessage()]);
        }
    }

    /**
     * Vista de comprobante de pago electrónico oficial (Boleta / Factura).
     */
    public function comprobante(Venta $venta): View
    {
        $venta->load(['cliente', 'usuario', 'detalles.producto']);
        return view('caja.comprobante', compact('venta'));
    }
}
