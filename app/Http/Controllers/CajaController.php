<?php

namespace App\Http\Controllers;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CajaController extends Controller
{
    /**
     * Muestra la bandeja de tickets pendientes por cobrar en caja (CUS-02)
     * y el resumen de cierre de caja del día por método de pago (RF-23).
     */
    public function index(Request $request): View
    {
        $buscar = $request->get('buscar');

        $queryPendientes = Venta::with(['cliente:id_cliente,dni,nombres', 'usuario:id,name', 'detalles.producto:id_producto,nombre'])
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

        $ultimasCobradas = Venta::with(['cliente:id_cliente,nombres', 'usuario:id,name'])
            ->where('estado', 'pagada')
            ->latest('updated_at')
            ->take(8)
            ->get();

        // RF-23: Cierre/Resumen de ingresos del turno (hoy) por método de pago
        $hoy = today()->toDateString();
        $totalesPorMetodo = Venta::selectRaw('metodo_pago, COUNT(*) as cantidad, COALESCE(SUM(total), 0) as monto')
            ->where('estado', 'pagada')
            ->whereDate('updated_at', $hoy)
            ->groupBy('metodo_pago')
            ->get()
            ->keyBy('metodo_pago');

        $resumenCaja = [
            'efectivo' => (float) ($totalesPorMetodo['efectivo']->monto ?? 0),
            'tarjeta' => (float) ($totalesPorMetodo['tarjeta']->monto ?? 0),
            'yape' => (float) ($totalesPorMetodo['yape']->monto ?? 0),
            'plin' => (float) ($totalesPorMetodo['plin']->monto ?? 0),
            'comprobantes' => (int) $totalesPorMetodo->sum('cantidad'),
            'total' => (float) $totalesPorMetodo->sum('monto'),
        ];

        return view('caja.index', compact('ticketsPendientes', 'ultimasCobradas', 'buscar', 'resumenCaja'));
    }

    /**
     * Procesa el cobro de un ticket, deduce el stock del inventario y emite comprobante.
     */
    public function cobrar(Request $request, Venta $venta): RedirectResponse
    {
        if ($venta->estado !== 'pendiente') {
            return back()->withErrors(['error' => 'El ticket seleccionado ya no se encuentra en estado pendiente.']);
        }

        // El monto recibido y el vuelto solo aplican al pago en efectivo: con
        // tarjeta, Yape o Plin se cobra el importe exacto y no hay vuelto.
        $validated = $request->validate([
            'metodo_pago' => 'required|in:efectivo,tarjeta,yape,plin',
            'monto_recibido' => [
                'required_if:metodo_pago,efectivo',
                'nullable',
                'numeric',
                'min:' . $venta->total,
                'max:99999999.99',
            ],
        ], [
            'monto_recibido.required_if' => 'Indique el monto que entregó el cliente en efectivo.',
            'monto_recibido.min' => 'El monto recibido debe ser igual o superior al total de la venta (S/ ' . number_format($venta->total, 2) . ').',
        ]);

        $montoRecibido = $validated['metodo_pago'] === 'efectivo'
            ? round((float) $validated['monto_recibido'], 2)
            : round((float) $venta->total, 2);

        $vuelto = $validated['metodo_pago'] === 'efectivo'
            ? round($montoRecibido - (float) $venta->total, 2)
            : 0.0;

        try {
            $numeroComprobante = DB::transaction(function () use ($venta, $validated, $montoRecibido, $vuelto, $request) {
                // Se bloquea la venta: si dos cajeros (o un doble clic) intentan cobrar
                // el mismo ticket, el segundo espera y luego lo encuentra ya pagado.
                $ventaBloqueada = Venta::whereKey($venta->id_venta)->lockForUpdate()->firstOrFail();

                if ($ventaBloqueada->estado !== 'pendiente') {
                    throw new \DomainException('El ticket seleccionado ya fue cobrado o anulado.');
                }

                // 1. Emitir el comprobante con el siguiente correlativo de su serie
                $numeroComprobante = Venta::siguienteNumeroComprobante($ventaBloqueada->tipo_comprob);

                // 2. Descontar el stock fisico de cada juguete vendido y registrar en Kardex
                foreach ($ventaBloqueada->detalles as $detalle) {
                    $producto = Producto::lockForUpdate()->findOrFail($detalle->id_producto);
                    $stockAnterior = $producto->stock_actual;

                    if (! $producto->descontarStock($detalle->cantidad)) {
                        throw new \DomainException("Stock insuficiente al momento de cobrar para '{$producto->nombre}'. Disponible: {$producto->stock_actual} un.");
                    }

                    MovimientoInventario::registrar(
                        $producto,
                        MovimientoInventario::TIPO_SALIDA_VENTA,
                        $detalle->cantidad,
                        $stockAnterior,
                        $producto->stock_actual,
                        $numeroComprobante,
                        "Venta {$ventaBloqueada->codigo_venta} cobrada en caja ({$validated['metodo_pago']})",
                        $request->user()?->id,
                    );
                }

                $ventaBloqueada->update([
                    'estado' => 'pagada',
                    'metodo_pago' => $validated['metodo_pago'],
                    'monto_recibido' => $montoRecibido,
                    'vuelto' => $vuelto,
                    'nro_comprob' => $numeroComprobante,
                ]);

                return $numeroComprobante;
            });
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['error' => 'No se pudo procesar el cobro. Intente nuevamente o comuníquese con soporte.']);
        }

        return redirect()->route('caja.comprobante', $venta->id_venta)
            ->with('success', "Cobro realizado con éxito. Comprobante {$numeroComprobante} emitido."
                . ($vuelto > 0 ? ' Vuelto a entregar: S/ ' . number_format($vuelto, 2) . '.' : ''));
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
