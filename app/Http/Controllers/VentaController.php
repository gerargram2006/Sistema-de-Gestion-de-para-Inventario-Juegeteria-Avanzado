<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Http\JsonResponse;
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
            ->whereNotIn('estado', ['agotado', 'inactivo'])
            ->orderBy('nombre')
            ->get()
            ->each(fn (Producto $producto) => $producto->setAttribute(
                'imagen_url',
                ($ruta = $producto->rutaImagen()) ? asset($ruta) : null
            ));

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
            'metodo_pago' => 'nullable|in:efectivo,tarjeta,yape,plin',
            'items' => 'required|array|min:1',
            'items.*.id_producto' => 'required|exists:productos,id_producto',
            'items.*.cantidad' => 'required|integer|min:1|max:10000',
        ]);

        $idCliente = $validated['id_cliente'];
        // Normalizar 'ticket' a 'boleta' para la serie del comprobante (B001)
        $tipoComprobante = $validated['tipo_comprobante'] === 'ticket' ? 'boleta' : $validated['tipo_comprobante'];
        $metodoPago = $validated['metodo_pago'] ?? 'efectivo';

        // Si el mismo juguete se agrego en varias lineas, se suman sus cantidades
        // para validar el stock contra el total pedido y no linea por linea.
        $cantidades = collect($validated['items'])
            ->groupBy('id_producto')
            ->map(fn ($lineas) => (int) $lineas->sum('cantidad'));

        $productos = Producto::whereIn('id_producto', $cantidades->keys())->get()->keyBy('id_producto');

        $subtotalTotal = 0;
        $detallesAGuardar = [];

        foreach ($cantidades as $idProducto => $cantidad) {
            $producto = $productos[$idProducto];

            if ($producto->estado === 'inactivo') {
                return back()->withInput()->withErrors([
                    'stock' => "El juguete '{$producto->nombre}' está inactivo y no se puede vender.",
                ]);
            }

            if (! $producto->tieneStockSuficiente($cantidad)) {
                return back()->withInput()->withErrors([
                    'stock' => "Stock insuficiente para el juguete '{$producto->nombre}'. Stock disponible: {$producto->stock_actual} un.",
                ]);
            }

            $precioUnitario = (float) $producto->precio;
            $subtotalLinea = $precioUnitario * $cantidad;
            $subtotalTotal += $subtotalLinea;

            $detallesAGuardar[] = [
                'producto' => $producto,
                'cantidad' => $cantidad,
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
                'metodo_pago' => $metodoPago,
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
            report($e);

            return back()->withInput()->withErrors(['error' => 'No se pudo registrar la venta. Intente nuevamente o comuníquese con soporte.']);
        }
    }

    /**
     * Listado histórico de ventas realizadas.
     */
    public function index(Request $request): View
    {
        $query = Venta::with(['cliente:id_cliente,dni,nombres', 'usuario:id,name']);

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
     * Anula un ticket de venta que aún está pendiente de cobro (CUS-01 / estado_ventas).
     */
    public function anular(Venta $venta): RedirectResponse
    {
        try {
            DB::transaction(function () use ($venta) {
                $ventaBloqueada = Venta::whereKey($venta->id_venta)->lockForUpdate()->firstOrFail();

                if (! $ventaBloqueada->puedeAnularse()) {
                    throw new \DomainException("El ticket {$ventaBloqueada->codigo_venta} está en estado '{$ventaBloqueada->estado}' y solo se pueden anular tickets pendientes de cobro.");
                }

                $ventaBloqueada->update(['estado' => 'anulada']);
            });
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', "Ticket {$venta->codigo_venta} anulado correctamente.");
    }

    /**
     * Consulta de clientes e historial de compras por DNI/nombre (RF-27).
     */
    public function clientes(Request $request): View
    {
        $buscar = trim((string) $request->query('buscar'));

        $clientes = Cliente::query()
            ->withCount('ventas')
            ->withSum(['ventas as total_comprado' => fn ($q) => $q->where('estado', 'pagada')], 'total')
            ->with(['ventas' => fn ($q) => $q->latest('fecha')->take(5)])
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('nombres', 'like', "%{$buscar}%")
                        ->orWhere('dni', 'like', "%{$buscar}%");
                });
            })
            ->orderBy('nombres')
            ->paginate(12)
            ->withQueryString();

        return view('ventas.clientes', compact('clientes', 'buscar'));
    }

    /**
     * Registro rápido de cliente desde el POS (Flujo alterno 3a de CUS-01).
     * Soporta respuesta JSON para agregarlo al selector sin perder el carrito.
     */
    public function storeCliente(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'dni' => 'required|digits:8|unique:clientes,dni',
            'nombre' => 'required|string|max:100',
            'telefono' => 'nullable|string|max:15',
            'email' => 'nullable|email|max:100',
        ]);

        $cliente = Cliente::create([
            'dni' => $validated['dni'],
            'nombres' => $validated['nombre'],
            'telefono' => $validated['telefono'] ?? null,
            'correo' => $validated['email'] ?? null,
            'estado' => 'activo',
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'cliente' => [
                    'id_cliente' => $cliente->id_cliente,
                    'dni' => $cliente->dni,
                    'nombre' => $cliente->nombres,
                ],
                'message' => "Cliente {$cliente->nombres} registrado y seleccionado correctamente.",
            ], 201);
        }

        return back()
            ->with('success', "Cliente {$validated['nombre']} registrado exitosamente.")
            ->with('nuevo_cliente_id', $cliente->id_cliente);
    }
}
