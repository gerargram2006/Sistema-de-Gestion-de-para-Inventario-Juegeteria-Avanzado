<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $venta->tipo_comprobante === 'factura' ? 'Factura Electrónica' : 'Boleta de Venta Electrónica' }} - {{ $venta->numero_comprobante ?? $venta->numero_ticket }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            .ticket-container { box-shadow: none !important; border: none !important; width: 100% !important; max-width: 100% !important; }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen py-8 px-4 flex flex-col items-center">

    <!-- Barra de Herramientas Superior (No se imprime) -->
    <div class="no-print max-w-lg w-full mb-4 flex justify-between items-center bg-white p-4 rounded-xl shadow-sm border border-gray-200">
        <a href="{{ route('caja.index') }}" class="text-xs font-semibold text-gray-600 hover:text-gray-900 flex items-center gap-1">
            ← Volver a Caja
        </a>
        <div class="flex gap-2">
            <button type="button" onclick="window.print()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-lg shadow-sm transition flex items-center gap-1.5">
                🖨️ Imprimir Comprobante
            </button>
        </div>
    </div>

    <!-- Comprobante Fiscal Formato Térmico / Electrónico -->
    <div class="ticket-container bg-white w-full max-w-md p-6 rounded-2xl shadow-md border border-gray-200 text-xs text-gray-800 font-sans">
        
        <!-- Encabezado de la Empresa -->
        <div class="text-center pb-4 border-b border-dashed border-gray-300">
            <div class="text-3xl mb-1">🧸</div>
            <h1 class="text-base font-black uppercase tracking-wider text-gray-900">Mundo del Juguete S.A.C.</h1>
            <p class="text-[11px] font-semibold text-gray-600">R.U.C. 20485961234</p>
            <p class="text-[11px] text-gray-500">Av. Los Niños 789, San Isidro, Lima</p>
            <p class="text-[11px] text-gray-500">Teléfono: (01) 456-7890</p>
        </div>

        <!-- Tipo de Comprobante y Numeración -->
        <div class="text-center my-3 py-2 border-y-2 border-gray-900">
            <h2 class="text-sm font-black uppercase tracking-wide">
                {{ $venta->tipo_comprobante === 'factura' ? 'FACTURA ELECTRÓNICA' : 'BOLETA DE VENTA ELECTRÓNICA' }}
            </h2>
            <p class="font-mono font-bold text-sm text-indigo-700 mt-0.5">
                {{ $venta->numero_comprobante ?? 'B001-' . str_pad($venta->id_venta, 6, '0', STR_PAD_LEFT) }}
            </p>
            <p class="text-[10px] text-gray-400 font-mono mt-0.5">Ref. Ticket: {{ $venta->numero_ticket }}</p>
        </div>

        <!-- Datos del Cliente y Emisión -->
        <div class="space-y-1 pb-3 border-b border-dashed border-gray-300 text-[11px]">
            <div class="flex justify-between">
                <span class="text-gray-500">Fecha de Emisión:</span>
                <span class="font-bold">{{ $venta->fecha ? $venta->fecha->format('d/m/Y H:i:s') : now()->format('d/m/Y H:i:s') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Cliente:</span>
                <span class="font-bold text-right">{{ $venta->cliente->nombre ?? 'Clientes Varios' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">{{ $venta->tipo_comprobante === 'factura' ? 'RUC:' : 'DNI:' }}</span>
                <span class="font-mono font-bold">{{ $venta->cliente->dni ?? '00000000' }}</span>
            </div>
            @if($venta->cliente && $venta->cliente->direccion)
                <div class="flex justify-between">
                    <span class="text-gray-500">Dirección:</span>
                    <span class="text-right truncate max-w-[200px]">{{ $venta->cliente->direccion }}</span>
                </div>
            @endif
            <div class="flex justify-between">
                <span class="text-gray-500">Cajero:</span>
                <span>{{ Auth::user()->name ?? 'Cajero de Turno' }}</span>
            </div>
        </div>

        <!-- Tabla de Productos / Detalle -->
        <div class="py-3 border-b border-dashed border-gray-300">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-[10px] uppercase font-bold text-gray-500 border-b border-gray-200">
                        <th class="pb-1">Cant.</th>
                        <th class="pb-1">Descripción</th>
                        <th class="pb-1 text-right">P. Unit</th>
                        <th class="pb-1 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-[11px]">
                    @foreach ($venta->detalles as $det)
                        <tr>
                            <td class="py-1.5 font-bold">{{ $det->cantidad }}</td>
                            <td class="py-1.5 pr-2">
                                <div class="font-medium text-gray-900 leading-tight">{{ $det->producto->nombre ?? 'Juguete' }}</div>
                                <div class="text-[9px] text-gray-400 font-mono">{{ $det->producto->sku ?? '' }}</div>
                            </td>
                            <td class="py-1.5 text-right font-mono">{{ number_format($det->precio_unitario, 2) }}</td>
                            <td class="py-1.5 text-right font-bold font-mono">{{ number_format($det->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totales y Resumen -->
        <div class="py-3 border-b border-dashed border-gray-300 space-y-1 text-[11px]">
            <div class="flex justify-between text-gray-600">
                <span>OP. GRAVADA:</span>
                <span class="font-mono">S/ {{ number_format($venta->subtotal, 2) }}</span>
            </div>
            <div class="flex justify-between text-gray-600">
                <span>I.G.V. (18%):</span>
                <span class="font-mono">S/ {{ number_format($venta->igv, 2) }}</span>
            </div>
            <div class="flex justify-between text-sm font-black text-gray-900 pt-1 border-t border-gray-200">
                <span>IMPORTE TOTAL:</span>
                <span class="font-mono text-base text-indigo-700">S/ {{ number_format($venta->total, 2) }}</span>
            </div>
        </div>

        <!-- Datos del Pago -->
        <div class="py-3 border-b border-dashed border-gray-300 space-y-1 text-[11px]">
            <div class="flex justify-between">
                <span class="text-gray-500">Forma de Pago:</span>
                <span class="font-bold uppercase">{{ $venta->metodo_pago ?? 'Efectivo' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Monto Recibido:</span>
                <span class="font-mono font-semibold">S/ {{ number_format($venta->monto_recibido ?? $venta->total, 2) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Vuelto Entregado:</span>
                <span class="font-mono font-bold text-emerald-700">S/ {{ number_format($venta->vuelto ?? 0, 2) }}</span>
            </div>
        </div>

        <!-- Pie del Comprobante Fiscal -->
        <div class="pt-4 text-center text-[10px] text-gray-400 space-y-1">
            <div class="font-mono bg-gray-50 p-2 rounded border border-gray-100 text-[9px] text-gray-500">
                Representación impresa de la Boleta Electrónica autorizada mediante Resolución de Superintendencia SUNAT.
            </div>
            <p class="font-bold text-gray-600 mt-2">¡Gracias por su compra en Mundo del Juguete! 🧸</p>
            <p>Conserve su comprobante para cualquier cambio o garantía.</p>
        </div>

    </div>

</body>
</html>
