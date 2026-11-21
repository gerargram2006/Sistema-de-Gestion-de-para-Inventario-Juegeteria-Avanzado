<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InventarioController extends Controller
{
    public function index(Request $request): View
    {
        $categorias = Categoria::select(['id_categoria', 'nombre'])
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        $todasCategorias = Categoria::withCount('productos')
            ->orderBy('nombre')
            ->get();

        $query = Producto::with('categoria:id_categoria,nombre');

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

        return view('inventario.index', compact('productos', 'categorias', 'todasCategorias'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_categoria' => 'required|exists:categorias,id_categoria',
            'sku' => 'required|string|max:30|unique:productos,sku',
            'nombre' => 'required|string|max:100',
            'precio' => 'required|numeric|min:0.1|max:99999999.99',
            'stock_actual' => 'required|integer|min:0|max:1000000',
            'stock_minimo' => 'required|integer|min:1|max:1000000',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $producto = new Producto($validated);
            $producto->actualizarEstadoStock();
            $producto->save();

            if ($producto->stock_actual > 0) {
                MovimientoInventario::registrar(
                    $producto,
                    MovimientoInventario::TIPO_INVENTARIO_INICIAL,
                    $producto->stock_actual,
                    0,
                    $producto->stock_actual,
                    $producto->sku,
                    'Registro inicial del juguete en catálogo',
                    $request->user()?->id,
                );
            }
        });

        return redirect()->route('inventario.index')->with('success', 'Juguete registrado exitosamente en el catálogo.');
    }

    public function update(Request $request, Producto $producto): RedirectResponse
    {
        $validated = $request->validate([
            'id_categoria' => 'required|exists:categorias,id_categoria',
            'nombre' => 'required|string|max:100',
            'precio' => 'required|numeric|min:0.1|max:99999999.99',
            'stock_actual' => 'required|integer|min:0|max:1000000',
            'stock_minimo' => 'required|integer|min:1|max:1000000',
        ]);

        DB::transaction(function () use ($validated, $producto, $request) {
            $productoBloqueado = Producto::lockForUpdate()->findOrFail($producto->id_producto);
            $stockAnterior = $productoBloqueado->stock_actual;

            $productoBloqueado->fill($validated);
            $productoBloqueado->actualizarEstadoStock();
            $productoBloqueado->save();

            $diferencia = $productoBloqueado->stock_actual - $stockAnterior;
            if ($diferencia !== 0) {
                MovimientoInventario::registrar(
                    $productoBloqueado,
                    $diferencia > 0 ? MovimientoInventario::TIPO_AJUSTE_POSITIVO : MovimientoInventario::TIPO_AJUSTE_NEGATIVO,
                    abs($diferencia),
                    $stockAnterior,
                    $productoBloqueado->stock_actual,
                    $productoBloqueado->sku,
                    'Actualización manual desde edición de catálogo',
                    $request->user()?->id,
                );
            }
        });

        return redirect()->route('inventario.index')->with('success', 'Datos del producto actualizados correctamente.');
    }

    /**
     * Registra una nueva categoría de juguetes (RF-02).
     */
    public function storeCategoria(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre_categoria' => 'required|string|max:50|unique:categorias,nombre',
            'descripcion_categoria' => 'nullable|string|max:255',
        ], [
            'nombre_categoria.required' => 'Ingrese el nombre de la categoría.',
            'nombre_categoria.unique' => 'Ya existe una categoría con ese nombre.',
        ]);

        Categoria::create([
            'nombre' => $validated['nombre_categoria'],
            'descripcion' => $validated['descripcion_categoria'] ?? null,
            'estado' => 'activo',
        ]);

        return redirect()->route('inventario.index')->with('success', "Categoría '{$validated['nombre_categoria']}' registrada correctamente.");
    }

    /**
     * Activa o desactiva una categoría sin eliminar su historial.
     */
    public function cambiarEstadoCategoria(Categoria $categoria): RedirectResponse
    {
        $nuevoEstado = $categoria->estado === 'activo' ? 'inactivo' : 'activo';
        $categoria->update(['estado' => $nuevoEstado]);

        $accion = $nuevoEstado === 'activo' ? 'activada' : 'desactivada';

        return back()->with('success', "Categoría '{$categoria->nombre}' {$accion}.");
    }

    /**
     * Da de baja o reactiva un juguete. No se elimina porque sus ventas y
     * ordenes de compra lo referencian; al reactivarlo se recalcula su
     * estado segun el stock actual.
     */
    public function cambiarEstado(Producto $producto): RedirectResponse
    {
        if ($producto->estaInactivo()) {
            $producto->estado = 'disponible';
            $producto->actualizarEstadoStock();
            $mensaje = "'{$producto->nombre}' fue reactivado y vuelve a estar a la venta.";
        } else {
            $producto->estado = 'inactivo';
            $mensaje = "'{$producto->nombre}' fue dado de baja: ya no aparece en el punto de venta ni en las alertas de reposición.";
        }

        $producto->save();

        return back()->with('success', $mensaje);
    }
}
