<?php

namespace App\Http\Controllers;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * CUS-05 / CUN-03: Control de Inventario (solo Administrador, RF-12).
 *
 * Centraliza auditorias fisicas (RF-13), registro de mermas y roturas (RF-14),
 * recalibracion de existencias y stock minimo (RF-15), reportes de kardex
 * exportables (RF-16) y deteccion de baja rotacion a 6 meses (RN-04).
 */
class ControlInventarioController extends Controller
{
    public function index(Request $request): View
    {
        $query = MovimientoInventario::with([
            'producto:id_producto,sku,nombre',
            'usuario:id,name',
        ]);

        if ($request->filled('producto')) {
            $query->where('id_producto', $request->producto);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('desde')) {
            $query->whereDate('fecha', '>=', $request->desde);
        }

        if ($request->filled('hasta')) {
            $query->whereDate('fecha', '<=', $request->hasta);
        }

        $movimientos = $query->latest('fecha')->latest('id_movimiento')->paginate(20)->withQueryString();

        $productos = Producto::with('categoria:id_categoria,nombre')
            ->select(['id_producto', 'id_categoria', 'sku', 'nombre', 'precio', 'stock_actual', 'stock_minimo', 'estado', 'created_at'])
            ->orderBy('nombre')
            ->get();

        // RN-04: juguetes activos con stock > 0 sin ventas cobradas en los ultimos 6 meses
        $fechaCorte = now()->subMonths(6);
        $bajaRotacion = Producto::with('categoria:id_categoria,nombre')
            ->select(['id_producto', 'id_categoria', 'sku', 'nombre', 'precio', 'stock_actual', 'stock_minimo', 'estado', 'created_at'])
            ->where('estado', '!=', 'inactivo')
            ->where('stock_actual', '>', 0)
            ->whereDoesntHave('detalleVentas', function ($q) use ($fechaCorte) {
                $q->whereHas('venta', function ($vq) use ($fechaCorte) {
                    $vq->where('estado', 'pagada')
                        ->where('fecha', '>=', $fechaCorte);
                });
            })
            ->orderBy('created_at', 'asc')
            ->get();

        $resumen = MovimientoInventario::selectRaw("
            COUNT(*) as total_movimientos,
            COALESCE(SUM(CASE WHEN tipo = 'merma' THEN cantidad ELSE 0 END), 0) as total_mermas,
            COALESCE(SUM(CASE WHEN tipo IN ('ajuste_positivo', 'ajuste_negativo') THEN 1 ELSE 0 END), 0) as total_auditorias
        ")->first();

        return view('inventario.control', [
            'movimientos' => $movimientos,
            'productos' => $productos,
            'bajaRotacion' => $bajaRotacion,
            'tipos' => MovimientoInventario::ETIQUETAS_TIPO,
            'totalMovimientos' => (int) ($resumen->total_movimientos ?? 0),
            'totalMermas' => (int) ($resumen->total_mermas ?? 0),
            'totalAuditorias' => (int) ($resumen->total_auditorias ?? 0),
        ]);
    }

    /**
     * Registra una auditoria fisica (conteo real + recalibracion de stock minimo)
     * o una merma/rotura con sustento obligatorio (RF-13, RF-14, RF-15).
     */
    public function registrarAjuste(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_producto' => ['required', 'integer', 'exists:productos,id_producto'],
            'operacion' => ['required', Rule::in(['auditoria', 'merma'])],
            'stock_fisico' => ['required_if:operacion,auditoria', 'nullable', 'integer', 'min:0', 'max:1000000'],
            'stock_minimo' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'cantidad_merma' => ['required_if:operacion,merma', 'nullable', 'integer', 'min:1', 'max:1000000'],
            'motivo' => ['required', 'string', 'min:4', 'max:255'],
        ], [
            'stock_fisico.required_if' => 'Indique el conteo físico verificado en bodega.',
            'cantidad_merma.required_if' => 'Indique cuántas unidades corresponden a la merma o rotura.',
            'motivo.required' => 'Debe registrar el sustento u observación de la auditoría o merma.',
        ]);

        try {
            $mensaje = DB::transaction(function () use ($validated, $request) {
                $producto = Producto::lockForUpdate()->findOrFail($validated['id_producto']);
                $stockAnterior = $producto->stock_actual;

                if ($validated['operacion'] === 'merma') {
                    $cantidad = (int) $validated['cantidad_merma'];

                    if (! $producto->tieneStockSuficiente($cantidad)) {
                        throw new \DomainException("No se puede registrar una merma de {$cantidad} un. para '{$producto->nombre}' porque el stock actual es {$stockAnterior} un.");
                    }

                    $producto->descontarStock($cantidad);

                    MovimientoInventario::registrar(
                        $producto,
                        MovimientoInventario::TIPO_MERMA,
                        $cantidad,
                        $stockAnterior,
                        $producto->stock_actual,
                        'MERMA-' . now()->format('YmdHis'),
                        $validated['motivo'],
                        $request->user()->id,
                    );

                    return "Merma registrada para '{$producto->nombre}': -{$cantidad} un. Stock actualizado a {$producto->stock_actual} un.";
                }

                // Auditoría física (RF-13 / RF-15)
                $stockFisico = (int) $validated['stock_fisico'];
                $diferencia = $stockFisico - $stockAnterior;
                $minimoCambio = isset($validated['stock_minimo']) && (int) $validated['stock_minimo'] !== $producto->stock_minimo;

                if (isset($validated['stock_minimo'])) {
                    $producto->stock_minimo = (int) $validated['stock_minimo'];
                }

                $producto->stock_actual = $stockFisico;
                $producto->actualizarEstadoStock();
                $producto->save();

                if ($diferencia !== 0) {
                    MovimientoInventario::registrar(
                        $producto,
                        $diferencia > 0 ? MovimientoInventario::TIPO_AJUSTE_POSITIVO : MovimientoInventario::TIPO_AJUSTE_NEGATIVO,
                        abs($diferencia),
                        $stockAnterior,
                        $stockFisico,
                        'AUD-' . now()->format('YmdHis'),
                        $validated['motivo'],
                        $request->user()->id,
                    );
                }

                if ($diferencia === 0 && ! $minimoCambio) {
                    return "Auditoría confirmada para '{$producto->nombre}': el stock físico coincide exactamente con el sistema ({$stockFisico} un.).";
                }

                $textoDiff = $diferencia > 0 ? "+{$diferencia} un." : ($diferencia < 0 ? "{$diferencia} un." : 'sin variación de stock');

                return "Auditoría aplicada a '{$producto->nombre}' ({$textoDiff}). Stock actual: {$producto->stock_actual} un. | Mínimo: {$producto->stock_minimo} un.";
            });
        } catch (\DomainException $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('control-inventario.index')->with('success', $mensaje);
    }

    /**
     * Exporta el Kardex filtrado en formato CSV compatible con Excel (RF-16).
     */
    public function exportarCsv(Request $request): StreamedResponse
    {
        $query = MovimientoInventario::with([
            'producto:id_producto,sku,nombre,precio',
            'usuario:id,name',
        ]);

        if ($request->filled('producto')) {
            $query->where('id_producto', $request->producto);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('desde')) {
            $query->whereDate('fecha', '>=', $request->desde);
        }

        if ($request->filled('hasta')) {
            $query->whereDate('fecha', '<=', $request->hasta);
        }

        $movimientos = $query->latest('fecha')->latest('id_movimiento')->get();
        $nombreArchivo = 'kardex-mundo-juguete-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($movimientos) {
            $salida = fopen('php://output', 'w');
            // BOM UTF-8 para que Excel abra acentos y eñes correctamente
            fwrite($salida, "\xEF\xBB\xBF");

            fputcsv($salida, [
                'Fecha',
                'SKU',
                'Juguete',
                'Tipo de Movimiento',
                'Cantidad',
                'Stock Anterior',
                'Stock Nuevo',
                'Valorizado (S/)',
                'Referencia',
                'Responsable',
                'Motivo / Observación',
            ]);

            foreach ($movimientos as $mov) {
                $signo = $mov->esEntrada() ? '+' : '-';
                $valor = (float) ($mov->producto->precio ?? 0) * $mov->cantidad;

                fputcsv($salida, [
                    $mov->fecha?->format('d/m/Y H:i') ?? '',
                    $mov->producto->sku ?? '',
                    $mov->producto->nombre ?? '',
                    $mov->etiquetaTipo(),
                    $signo . $mov->cantidad,
                    $mov->stock_anterior,
                    $mov->stock_nuevo,
                    number_format($valor, 2, '.', ''),
                    $mov->referencia ?? '',
                    $mov->usuario->name ?? 'Sistema',
                    $mov->motivo ?? '',
                ]);
            }

            fclose($salida);
        }, $nombreArchivo, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
