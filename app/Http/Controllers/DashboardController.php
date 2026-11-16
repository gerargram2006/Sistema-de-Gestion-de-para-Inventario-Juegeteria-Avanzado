<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $totalProductos = Producto::count();
        $stockTotal = Producto::sum('stock_actual');
        $productosCriticos = Producto::stockCritico()->count();
        $ventasPendientes = Venta::where('estado', 'pendiente')->count();
        $ventasHoyTotal = Venta::whereDate('fecha', today())->where('estado', 'pagada')->sum('total');

        $ultimosCriticos = Producto::with('categoria')
            ->stockCritico()
            ->orderBy('stock_actual', 'asc')
            ->take(5)
            ->get();

        $ultimasVentas = Venta::with(['cliente', 'usuario'])
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
