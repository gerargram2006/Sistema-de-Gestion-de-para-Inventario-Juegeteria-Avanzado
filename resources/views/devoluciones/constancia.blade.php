<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $devolucion->tipo === 'cambio' ? 'Constancia de Cambio' : 'Nota de Crédito' }} - NC-{{ str_pad($devolucion->id_devolucion, 6, '0', STR_PAD_LEFT) }} | {{ config('app.name', 'Mundo del Juguete') }}</title>
    <link rel="icon" type="{{ file_exists(public_path('images/logo.png')) ? 'image/png' : 'image/svg+xml' }}" href="{{ file_exists(public_path('images/logo.png')) ? asset('images/logo.png') : asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            .doc-container { box-shadow: none !important; border: none !important; width: 100% !important; max-width: 100% !important; }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen py-8 px-4 flex flex-col items-center">
    <div class="no-print max-w-lg w-full mb-4 flex justify-between items-center bg-white p-4 rounded-xl shadow-sm border border-gray-200">
        <a href="{{ route('devoluciones.index', ['buscar' => $devolucion->venta->codigo_venta]) }}" class="text-xs font-semibold text-gray-600 hover:text-gray-900">
            ← Volver a Devoluciones
        </a>
        <button type="button" x-data x-on:click="window.print()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-lg shadow-sm transition inline-flex items-center gap-1.5">
            <x-icon name="printer" class="w-4 h-4" />Imprimir Documento
        </button>
    </div>

    <div class="doc-container bg-white w-full max-w-md p-6 rounded-2xl shadow-md border border-gray-200 text-xs text-gray-800 font-sans">
        <div class="text-center pb-4 border-b border-dashed border-gray-300">
            <div class="flex justify-center mb-2"><x-application-logo class="w-12 h-12" /></div>
            <h1 class="text-base font-black uppercase tracking-wider text-gray-900">Mundo del Juguete S.A.C.</h1>
            <p class="text-[11px] font-semibold text-gray-600">R.U.C. 20485961234</p>
            <p class="text-[11px] text-gray-500">Av. Los Niños 789, San Isidro, Lima</p>
        </div>

        <div class="text-center my-3 py-2 border-y-2 border-gray-900">
            <h2 class="text-sm font-black uppercase tracking-wide">
                {{ $devolucion->tipo === 'cambio' ? 'CONSTANCIA DE CAMBIO DE PRODUCTO' : 'NOTA DE CRÉDITO ELECTRÓNICA' }}
            </h2>
            <p class="font-mono font-bold text-sm text-indigo-700 mt-0.5">
                {{ $devolucion->tipo === 'cambio' ? 'CAM' : 'NC01' }}-{{ str_pad($devolucion->id_devolucion, 6, '0', STR_PAD_LEFT) }}
            </p>
            <p class="text-[10px] text-gray-500 font-mono mt-0.5">
                Comprobante afectado: {{ $devolucion->venta->nro_comprob ?? $devolucion->venta->codigo_venta }}
            </p>
        </div>

        <div class="space-y-1 pb-3 border-b border-dashed border-gray-300 text-[11px]">
            <div class="flex justify-between">
                <span class="text-gray-500">Fecha de Emisión:</span>
                <span class="font-bold">{{ $devolucion->fecha->format('d/m/Y H:i:s') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Cliente:</span>
                <span class="font-bold">{{ $devolucion->venta->cliente->nombres ?? 'Clientes Varios' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">DNI:</span>
                <span class="font-mono font-bold">{{ $devolucion->venta->cliente->dni ?? '—' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Atendido por:</span>
                <span>{{ $devolucion->usuario->name ?? 'Personal' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Resolución:</span>
                <span class="font-bold uppercase text-emerald-700">{{ $devolucion->estado }}</span>
            </div>
        </div>

        <div class="py-3 border-b border-dashed border-gray-300 space-y-2 text-[11px]">
            <div class="flex justify-between">
                <div>
                    <div class="font-bold text-gray-900">{{ $devolucion->producto->nombre ?? 'Juguete' }}</div>
                    <div class="text-[10px] font-mono text-gray-400">SKU: {{ $devolucion->producto->sku ?? '—' }}</div>
                </div>
                <div class="text-right">
                    <div class="font-bold">{{ $devolucion->cantidad }} un. x S/ {{ number_format($precioUnitario, 2) }}</div>
                    <div class="font-mono font-black text-indigo-700">S/ {{ number_format($montoTotal, 2) }}</div>
                </div>
            </div>
            @if ($devolucion->motivo)
                <div class="p-2 rounded bg-gray-50 border border-gray-100 text-[10px]">
                    <span class="font-semibold text-gray-600">Motivo registrado:</span> {{ $devolucion->motivo }}
                </div>
            @endif
        </div>

        <div class="pt-4 text-center text-[10px] text-gray-500 space-y-1">
            @if ($devolucion->tipo === 'cambio')
                <p class="font-bold text-gray-700">Se entregó {{ $devolucion->cantidad }} unidad(es) de reemplazo conforme a garantía (RF-19).</p>
            @else
                <p class="font-bold text-gray-700">Importe reembolsado al cliente: S/ {{ number_format($montoTotal, 2) }}.</p>
                <p>El artículo fue reingresado al inventario (RF-21).</p>
            @endif
        </div>
    </div>
</body>
</html>
