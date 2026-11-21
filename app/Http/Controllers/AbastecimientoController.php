<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AbastecimientoController extends Controller
{
    /**
     * Muestra la bandeja de Abastecimiento: Alertas de Stock Crítico (CUS-03)
     * y Órdenes de Compra/Recepción pendientes (CUS-04).
     */
    public function index(): View
    {
        $criticos = Producto::with('categoria:id_categoria,nombre')
            ->select(['id_producto', 'id_categoria', 'sku', 'nombre', 'stock_actual', 'stock_minimo', 'estado'])
            ->stockCritico()
            ->orderBy('stock_actual', 'asc')
            ->get();

        $ordenesPendientes = Compra::with(['usuario:id,name', 'proveedorRegistrado', 'detalles.producto:id_producto,nombre'])
            ->whereIn('estado', Compra::ESTADOS_POR_RECIBIR)
            ->latest('fecha')
            ->get();

        $ordenesRecibidas = Compra::with(['usuario:id,name', 'proveedorRegistrado', 'detalles'])
            ->where('estado', 'recibida')
            ->latest('updated_at')
            ->take(8)
            ->get();

        $todosProductos = Producto::select(['id_producto', 'nombre', 'stock_actual'])
            ->orderBy('nombre')
            ->get();

        $proveedoresActivos = Proveedor::activos()
            ->orderBy('razon_social')
            ->get(['id_proveedor', 'razon_social']);

        return view('abastecimiento.index', compact(
            'criticos',
            'ordenesPendientes',
            'ordenesRecibidas',
            'todosProductos',
            'proveedoresActivos'
        ));
    }

    /**
     * Genera una nueva Orden de Compra para reabastecer stock crítico.
     */
    public function crearOrden(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_proveedor' => ['required', Rule::exists('proveedores', 'id_proveedor')->where('activo', true)],
            'items' => 'required|array|min:1',
            'items.*.id_producto' => 'required|exists:productos,id_producto',
            'items.*.cantidad' => 'required|integer|min:1|max:10000',
            'items.*.precio_costo' => 'required|numeric|min:0.01|max:99999999.99',
            'es_urgente' => 'nullable|boolean',
            'autorizacion_extraordinaria' => 'nullable|boolean',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $totalOrden = collect($validated['items'])->sum(fn ($i) => $i['cantidad'] * $i['precio_costo']);

        if ($totalOrden > 2000 && ! $request->boolean('autorizacion_extraordinaria')) {
            return back()->withInput()->withErrors([
                'autorizacion_extraordinaria' => 'El total supera S/ 2,000.00. Marca la autorización extraordinaria para emitir la orden.',
            ]);
        }

        $proveedor = Proveedor::findOrFail($validated['id_proveedor']);
        $notas = array_filter([
            $request->boolean('es_urgente') ? '[COMPRA URGENTE]' : null,
            $totalOrden > 2000 ? '[PRESUPUESTO EXTRAORDINARIO]' : null,
            trim($validated['observaciones'] ?? ''),
        ]);

        DB::beginTransaction();
        try {
            $compra = Compra::create([
                'id_proveedor' => $proveedor->id_proveedor,
                'proveedor' => $proveedor->razon_social,
                'id_usuario' => Auth::id(),
                'fecha' => now(),
                'total' => $totalOrden,
                'estado' => 'generada',
                'observaciones' => $notas ? implode(' ', $notas) : null,
            ]);

            foreach ($validated['items'] as $item) {
                DetalleCompra::create([
                    'id_compra' => $compra->id_compra,
                    'id_producto' => $item['id_producto'],
                    'cantidad' => $item['cantidad'],
                    'costo_unit' => $item['precio_costo'],
                    'subtotal' => $item['cantidad'] * $item['precio_costo'],
                ]);
            }

            DB::commit();

            return redirect()->route('abastecimiento.index')
                ->with('success', "Orden de abastecimiento {$compra->nro_orden} generada exitosamente por un total de S/ ".number_format($totalOrden, 2));
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return back()->withInput()->withErrors(['error' => 'No se pudo generar la orden de compra. Intente nuevamente o comuníquese con soporte.']);
        }
    }

    /**
     * CUS-04: Confirmar la recepción de mercadería física en el almacén.
     * Registra guía de remisión (RF-30), observaciones de daños/faltantes (Flujo 6a),
     * incrementa el stock, asienta el Kardex (RF-11) y actualiza el estado de la orden.
     */
    public function recibirOrden(Request $request, Compra $compra): RedirectResponse
    {
        $validated = $request->validate([
            'guia_remision' => ['nullable', 'string', 'max:50'],
            'observaciones_recepcion' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            DB::transaction(function () use ($compra, $validated, $request) {
                // Se bloquea la orden para que una doble recepcion no sume el stock dos veces
                $compra = Compra::whereKey($compra->id_compra)->lockForUpdate()->firstOrFail();

                if (! $compra->puedeRecibirse()) {
                    throw new \DomainException("La orden {$compra->nro_orden} está en estado '{$compra->estado}' y no puede recibirse.");
                }

                // Incrementar el stock fisico de cada juguete de la orden y registrar en Kardex
                foreach ($compra->detalles as $detalle) {
                    $producto = Producto::lockForUpdate()->findOrFail($detalle->id_producto);
                    $stockAnterior = $producto->stock_actual;
                    $producto->incrementarStock($detalle->cantidad);

                    $motivo = 'Recepción de orden de compra ' . $compra->nro_orden;
                    if (! empty($validated['guia_remision'])) {
                        $motivo .= ' · Guía ' . $validated['guia_remision'];
                    }
                    if (! empty($validated['observaciones_recepcion'])) {
                        $motivo .= ' · ' . $validated['observaciones_recepcion'];
                    }

                    MovimientoInventario::registrar(
                        $producto,
                        MovimientoInventario::TIPO_ENTRADA_COMPRA,
                        $detalle->cantidad,
                        $stockAnterior,
                        $producto->stock_actual,
                        $compra->nro_orden,
                        mb_substr($motivo, 0, 255),
                        $request->user()?->id,
                    );
                }

                $compra->update([
                    'estado' => 'recibida',
                    'guia_remision' => $validated['guia_remision'] ?? null,
                    'observaciones_recepcion' => $validated['observaciones_recepcion'] ?? null,
                    'fecha_recepcion' => now(),
                ]);
            });
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['error' => 'No se pudo registrar la recepción. Intente nuevamente o comuníquese con soporte.']);
        }

        return redirect()->route('abastecimiento.index')
            ->with('success', "Mercadería de la orden {$compra->nro_orden} recibida con éxito. El inventario ha sido actualizado.");
    }

    /**
     * Cancela una orden de compra que aún no ha sido recibida.
     */
    public function cancelarOrden(Compra $compra): RedirectResponse
    {
        try {
            DB::transaction(function () use ($compra) {
                $compraBloqueada = Compra::whereKey($compra->id_compra)->lockForUpdate()->firstOrFail();

                if (! $compraBloqueada->puedeRecibirse()) {
                    throw new \DomainException("La orden {$compraBloqueada->nro_orden} está en estado '{$compraBloqueada->estado}' y no puede cancelarse.");
                }

                $compraBloqueada->update(['estado' => 'cancelada']);
            });
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('abastecimiento.index')
            ->with('success', "Orden de compra {$compra->nro_orden} cancelada correctamente.");
    }

    /**
     * Constancia imprimible de ingreso de mercadería en almacén (RF-25 / CUS-04).
     */
    public function constancia(Compra $compra): View
    {
        abort_unless($compra->estado === 'recibida', 404);

        $compra->load(['usuario:id,name', 'proveedorRegistrado', 'detalles.producto:id_producto,sku,nombre']);

        return view('abastecimiento.constancia', compact('compra'));
    }
}
