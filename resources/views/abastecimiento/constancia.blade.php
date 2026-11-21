<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Constancia de Ingreso - {{ $compra->numero_orden }} | {{ config('app.name', 'Mundo del Juguete') }}</title>
    <link rel="icon" type="{{ file_exists(public_path('images/logo.png')) ? 'image/png' : 'image/svg+xml' }}" href="{{ file_exists(public_path('images/logo.png')) ? asset('images/logo.png') : asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            .doc-container { box-shadow: none !important; border: none !important; max-width: 100% !important; }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen py-8 px-4 flex flex-col items-center">
    <div class="no-print max-w-2xl w-full mb-4 flex justify-between items-center bg-white p-4 rounded-xl shadow-sm border border-gray-200">
        <a href="{{ route('abastecimiento.index') }}" class="text-xs font-semibold text-gray-600 hover:text-gray-900">
            ← Volver a Abastecimiento
        </a>
        <button type="button" x-data x-on:click="window.print()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-lg shadow-sm transition inline-flex items-center gap-1.5">
            <x-icon name="printer" class="w-4 h-4" />Imprimir Constancia
        </button>
    </div>

    <div class="doc-container bg-white w-full max-w-2xl p-8 rounded-2xl shadow-md border border-gray-200 text-xs text-gray-800 space-y-6">
        <div class="flex items-start justify-between border-b border-gray-200 pb-4">
            <div class="flex items-center gap-3">
                <x-application-logo class="w-11 h-11" />
                <div>
                    <h1 class="text-lg font-black uppercase text-gray-900">Mundo del Juguete S.A.C.</h1>
                    <p class="text-xs text-gray-500">Constancia de Conformidad de Ingreso a Almacén (RF-25 / CUS-04)</p>
                </div>
            </div>
            <div class="text-right">
                <span class="font-mono font-bold text-sm text-indigo-700 block">{{ $compra->numero_orden }}</span>
                <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">RECIBIDA</span>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 text-xs bg-gray-50 p-4 rounded-xl border border-gray-100">
            <div>
                <span class="text-gray-400 uppercase font-semibold block">Proveedor</span>
                <span class="font-bold text-gray-900">{{ $compra->proveedorRegistrado?->razon_social ?? $compra->proveedor }}</span>
                @if ($compra->proveedorRegistrado?->ruc)
                    <span class="block font-mono text-gray-500">RUC: {{ $compra->proveedorRegistrado->ruc }}</span>
                @endif
            </div>
            <div class="text-right">
                <span class="text-gray-400 uppercase font-semibold block">Fecha de Recepción</span>
                <span class="font-bold text-gray-900">{{ $compra->fecha_recepcion?->format('d/m/Y H:i') ?? '—' }}</span>
                <span class="block text-gray-500">Guía de Remisión: <strong class="font-mono">{{ $compra->guia_remision ?? 'No registrada' }}</strong></span>
            </div>
            <div>
                <span class="text-gray-400 uppercase font-semibold block">Emitida por</span>
                <span>{{ $compra->usuario->name ?? 'Administrador' }}</span>
            </div>
            <div class="text-right">
                <span class="text-gray-400 uppercase font-semibold block">Fecha de Emisión OC</span>
                <span>{{ $compra->fecha?->format('d/m/Y H:i') ?? '—' }}</span>
            </div>
        </div>

        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b-2 border-gray-200 text-gray-500 uppercase">
                    <th class="py-2">SKU</th>
                    <th class="py-2">Juguete Ingresado</th>
                    <th class="py-2 text-center">Cantidad</th>
                    <th class="py-2 text-right">Costo Unit.</th>
                    <th class="py-2 text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($compra->detalles as $det)
                    <tr>
                        <td class="py-2 font-mono text-gray-500">{{ $det->producto->sku ?? '—' }}</td>
                        <td class="py-2 font-semibold text-gray-900">{{ $det->producto->nombre ?? 'Juguete' }}</td>
                        <td class="py-2 text-center font-bold text-emerald-700">+{{ $det->cantidad }} un.</td>
                        <td class="py-2 text-right font-mono">S/ {{ number_format($det->precio_costo, 2) }}</td>
                        <td class="py-2 text-right font-mono font-bold">S/ {{ number_format($det->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-gray-200 font-black text-sm">
                    <td colspan="2" class="pt-3">TOTAL INGRESADO</td>
                    <td class="pt-3 text-center">{{ $compra->detalles->sum('cantidad') }} un.</td>
                    <td></td>
                    <td class="pt-3 text-right text-indigo-700 font-mono">S/ {{ number_format($compra->total, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        @if ($compra->observaciones || $compra->observaciones_recepcion)
            <div class="p-3 rounded-lg bg-amber-50/60 border border-amber-200 text-xs space-y-1">
                @if ($compra->observaciones)
                    <div><strong>Notas de la orden:</strong> {{ $compra->observaciones }}</div>
                @endif
                @if ($compra->observaciones_recepcion)
                    <div><strong>Observaciones de recepción física:</strong> {{ $compra->observaciones_recepcion }}</div>
                @endif
            </div>
        @endif

        <div class="pt-8 grid grid-cols-2 gap-8 text-center text-[11px] text-gray-500">
            <div class="border-t border-gray-300 pt-2">
                Firma del Encargado de Almacén<br>
                <span class="font-semibold text-gray-700">{{ Auth::user()->name }}</span>
            </div>
            <div class="border-t border-gray-300 pt-2">
                Conformidad de Proveedor / Transportista<br>
                <span class="font-semibold text-gray-700">{{ $compra->proveedorRegistrado?->razon_social ?? $compra->proveedor }}</span>
            </div>
        </div>
    </div>
</body>
</html>
