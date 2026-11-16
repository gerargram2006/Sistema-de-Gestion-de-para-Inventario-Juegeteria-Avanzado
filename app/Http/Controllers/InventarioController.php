<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventarioController extends Controller
{
    public function index(Request $request): View
    {
        $categorias = Categoria::where('estado', 'activo')->get();

        $query = Producto::with('categoria');

        if ($request->filled('categoria')) {
            $query->where('id_categoria', $request->categoria);
        }

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('sku', 'like', "%{$buscar}%");
            });
        }

        if ($request->get('filtro_stock') === 'critico') {
            $query->stockCritico();
        }

        $productos = $query->orderBy('nombre')->paginate(12)->withQueryString();

        return view('inventario.index', compact('productos', 'categorias'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_categoria' => 'required|exists:categorias,id_categoria',
            'sku' => 'required|string|max:30|unique:productos,sku',
            'nombre' => 'required|string|max:100',
            'precio' => 'required|numeric|min:0.1',
            'stock_actual' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:1',
        ]);

        $estado = 'disponible';
        if ($validated['stock_actual'] <= 0) {
            $estado = 'agotado';
        } elseif ($validated['stock_actual'] <= $validated['stock_minimo']) {
            $estado = 'stock bajo';
        }

        Producto::create([
            ...$validated,
            'estado' => $estado,
        ]);

        return redirect()->route('inventario.index')->with('success', 'Juguete registrado exitosamente en el catálogo.');
    }

    public function update(Request $request, Producto $producto): RedirectResponse
    {
        $validated = $request->validate([
            'id_categoria' => 'required|exists:categorias,id_categoria',
            'nombre' => 'required|string|max:100',
            'precio' => 'required|numeric|min:0.1',
            'stock_actual' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:1',
        ]);

        $producto->fill($validated);
        $producto->actualizarEstadoStock();
        $producto->save();

        return redirect()->route('inventario.index')->with('success', 'Datos del producto actualizados correctamente.');
    }
}
