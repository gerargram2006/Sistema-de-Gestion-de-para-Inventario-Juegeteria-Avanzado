<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        // Consolidar métricas de productos en una sola consulta SQL
        $productoStats = Producto::selectRaw('
            COUNT(*) as total_productos,
            COALESCE(SUM(stock_actual), 0) as stock_total,
            COALESCE(SUM(CASE WHEN stock_actual <= stock_minimo THEN 1 ELSE 0 END), 0) as productos_criticos
        ')->first();

        $totalProductos = (int) ($productoStats->total_productos ?? 0);
        $stockTotal = (int) ($productoStats->stock_total ?? 0);
        $productosCriticos = (int) ($productoStats->productos_criticos ?? 0);

        // Consolidar métricas de ventas en una sola consulta SQL
        $hoy = today()->toDateString();
        $ventaStats = Venta::selectRaw("
            COALESCE(SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END), 0) as ventas_pendientes,
            COALESCE(SUM(CASE WHEN estado = 'pagada' AND DATE(fecha) = ? THEN total ELSE 0 END), 0) as ventas_hoy_total
        ", [$hoy])->first();

        $ventasPendientes = (int) ($ventaStats->ventas_pendientes ?? 0);
        $ventasHoyTotal = (float) ($ventaStats->ventas_hoy_total ?? 0);

        $ultimosCriticos = Producto::with('categoria:id_categoria,nombre')
            ->select(['id_producto', 'id_categoria', 'sku', 'nombre', 'stock_actual', 'stock_minimo', 'estado'])
            ->stockCritico()
            ->orderBy('stock_actual', 'asc')
            ->take(5)
            ->get();

        $ultimasVentas = Venta::with(['cliente:id_cliente,nombres', 'usuario:id,name'])
            ->select(['id_venta', 'codigo_venta', 'id_cliente', 'id_usuario', 'fecha', 'total', 'estado'])
            ->latest('fecha')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'totalProductos',
            'stockTotal',
            'productosCriticos',
            'ventasPendientes',
            'ventasHoyTotal',
            'ultimosCriticos',
            'ultimasVentas'
        ));
    }
}
