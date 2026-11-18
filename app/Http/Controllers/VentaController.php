<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VentaController extends Controller
{
    /**
     * Muestra la interfaz interactiva de Punto de Venta (POS).
     */
    public function pos(): View
    {
        $clientes = Cliente::select(['id_cliente', 'dni', 'nombres'])
            ->orderBy('nombres')
            ->get();

        $productos = Producto::with('categoria:id_categoria,nombre')
            ->select(['id_producto', 'id_categoria', 'sku', 'nombre', 'precio', 'stock_actual', 'stock_minimo', 'estado'])
            ->where('stock_actual', '>', 0)
            ->where('estado', '!=', 'agotado')
            ->orderBy('nombre')
            ->get();

        return view('ventas.pos', compact('clientes', 'productos'));
    }

    /**
     * Registra un nuevo Ticket de Venta (CUS-01: Generar Ticket de Venta).
     * El ticket queda en estado 'pendiente' a la espera de ser cobrado en caja (CUS-02).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_cliente' => 'required|exists:clientes,id_cliente',
            'tipo_comprobante' => 'required|in:ticket,boleta,factura',
            'items' => 'required|array|min:1',
            'items.*.id_producto' => 'required|exists:productos,id_producto',
            'items.*.cantidad' => 'required|integer|min:1',
        ]);

        $idCliente = $validated['id_cliente'];
        $tipoComprobante = $validated['tipo_comprobante'];
        $itemsData = $validated['items'];

        $subtotalTotal = 0;
        $detallesAGuardar = [];

        // Validar stock disponible para cada producto en la orden
        foreach ($itemsData as $item) {
            $producto = Producto::findOrFail($item['id_producto']);

            if (!$producto->tieneStockSuficiente($item['cantidad'])) {
                return back()->withInput()->withErrors([
                    'stock' => "Stock insuficiente para el juguete '{$producto->nombre}'. Stock disponible: {$producto->stock_actual} un.",
                ]);
            }

            $precioUnitario = (float) $producto->precio;
            $subtotalLinea = $precioUnitario * $item['cantidad'];
            $subtotalTotal += $subtotalLinea;

            $detallesAGuardar[] = [
                'producto' => $producto,
                'cantidad' => $item['cantidad'],
                'precio_unitario' => $precioUnitario,
                'subtotal' => $subtotalLinea,
            ];
        }

        $total = round($subtotalTotal, 2);

        DB::beginTransaction();
        try {
            $venta = Venta::create([
                'id_usuario' => Auth::id(),
                'id_cliente' => $idCliente,
                'fecha' => now(),
                'tipo_comprob' => $tipoComprobante,
                'total' => $total,
                'metodo_pago' => 'efectivo',
                'estado' => 'pendiente', // Espera cobro en caja
            ]);

            foreach ($detallesAGuardar as $d) {
                DetalleVenta::create([
                    'id_venta' => $venta->id_venta,
                    'id_producto' => $d['producto']->id_producto,
                    'cantidad' => $d['cantidad'],
                    'precio_unit' => $d['precio_unitario'],
                    'subtotal' => $d['subtotal'],
                ]);
            }

            DB::commit();

            return redirect()->route('ventas.show', $venta->id_venta)
                ->with('success', "Ticket {$venta->codigo_venta} emitido correctamente por S/ " . number_format($total, 2) . ". Dirigirse a Caja para efectuar el cobro.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Error al registrar la venta: ' . $e->getMessage()]);
        }
    }

    /**
     * Listado histórico de ventas realizadas.
     */
    public function index(Request $request): View
    {
        $query = Venta::with(['cliente', 'usuario', 'detalles.producto']);

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('fecha')) {
            $query->whereDate('fecha', $request->fecha);
        }

        $ventas = $query->latest('fecha')->paginate(15)->withQueryString();

        return view('ventas.index', compact('ventas'));
    }

    /**
     * Visualización detallada de un ticket de venta.
     */
    public function show(Venta $venta): View
    {
        $venta->load(['cliente', 'usuario', 'detalles.producto']);
        return view('ventas.show', compact('venta'));
    }

    /**
     * Registro rápido de cliente desde el POS.
     */
    public function storeCliente(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'dni' => 'required|digits:8|unique:clientes,dni',
            'nombre' => 'required|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
        ]);

        Cliente::create([
            'dni' => $validated['dni'],
            'nombres' => $validated['nombre'],
            'telefono' => $validated['telefono'] ?? null,
            'correo' => $validated['email'] ?? null,
            'estado' => 'activo',
        ]);

        return back()->with('success', "Cliente {$validated['nombre']} registrado exitosamente.");
    }
}
