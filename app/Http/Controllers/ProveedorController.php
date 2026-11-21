<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProveedorRequest;
use App\Models\Proveedor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProveedorController extends Controller
{
    public function index(Request $request): View
    {
        $busqueda = trim((string) $request->query('q'));
        $estado = $request->query('estado');

        $proveedores = Proveedor::query()
            ->withCount('compras')
            ->when($busqueda !== '', function ($query) use ($busqueda) {
                $query->where(function ($q) use ($busqueda) {
                    $q->where('razon_social', 'like', "%{$busqueda}%")
                        ->orWhere('ruc', 'like', "%{$busqueda}%");
                });
            })
            ->when($estado === 'activos', fn ($q) => $q->where('activo', true))
            ->when($estado === 'inactivos', fn ($q) => $q->where('activo', false))
            ->orderBy('razon_social')
            ->paginate(10)
            ->withQueryString();

        return view('proveedores.index', compact('proveedores', 'busqueda', 'estado'));
    }

    public function create(): View
    {
        return view('proveedores.create');
    }

    public function store(ProveedorRequest $request): RedirectResponse
    {
        Proveedor::create($request->validated());

        return redirect()->route('proveedores.index')
            ->with('status', 'Proveedor registrado correctamente.');
    }

    /**
     * Alta rápida desde el modal de la orden de compra, sin recargar la
     * pantalla de Abastecimiento.
     */
    public function storeRapido(ProveedorRequest $request): JsonResponse
    {
        $datos = $request->validated();
        $datos['activo'] = $request->boolean('activo', true);

        $proveedor = Proveedor::create($datos);

        return response()->json([
            'proveedor' => [
                'id_proveedor' => $proveedor->id_proveedor,
                'razon_social' => $proveedor->razon_social,
                'activo' => $proveedor->activo,
            ],
        ], 201);
    }

    public function edit(Proveedor $proveedor): View
    {
        return view('proveedores.edit', compact('proveedor'));
    }

    public function update(ProveedorRequest $request, Proveedor $proveedor): RedirectResponse
    {
        $proveedor->update($request->validated());

        return redirect()->route('proveedores.index')
            ->with('status', 'Proveedor actualizado correctamente.');
    }

    /**
     * Activa o desactiva al proveedor. No se elimina para conservar el
     * historial de órdenes de compra.
     */
    public function cambiarEstado(Proveedor $proveedor): RedirectResponse
    {
        $proveedor->update(['activo' => ! $proveedor->activo]);

        return back()->with('status', $proveedor->activo
            ? 'Proveedor activado.'
            : 'Proveedor desactivado. Ya no se le podrán emitir órdenes de compra.');
    }
}
