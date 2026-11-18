<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                    <x-icon name="receipt-percent" class="w-7 h-7 text-indigo-600" /> Detalle de Ticket: {{ $venta->numero_ticket }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">Información detallada de la orden y estado de pago.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('ventas.pos') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-lg uppercase tracking-wider shadow-sm transition">
                    <x-icon name="shopping-cart" class="w-4 h-4 inline-block align-[-2px] me-1" />Nuevo Ticket POS
                </a>
                <a href="{{ route('ventas.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs rounded-lg uppercase tracking-wider transition">
                    ← Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-lg shadow-sm">
                    <p class="text-sm text-emerald-800 font-medium">{{ session('success') }}</p>
                </div>
            @endif

            <!-- Tarjeta Ticket Estilo Recibo -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <!-- Cabecera -->
                <div class="bg-indigo-900 text-white p-6 text-center">
                    <div class="flex justify-center mb-2"><x-application-logo class="w-12 h-12" /></div>
                    <h3 class="text-xl font-black uppercase tracking-wider">Mundo del Juguete S.A.C.</h3>
                    <p class="text-xs text-indigo-200">RUC: 20485961234 | Av. Los Niños 789, Lima, Perú</p>
                    <div class="mt-4 inline-block px-3 py-1 bg-indigo-800/80 rounded-full text-xs font-mono tracking-widest text-indigo-100">
                        {{ $venta->numero_ticket }}
                    </div>
                </div>

                <!-- Metadatos de la Venta -->
                <div class="p-6 border-b border-gray-100 grid grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-gray-400 font-semibold block uppercase">Cliente</span>
                        <span class="font-bold text-gray-800 text-sm">{{ $venta->cliente->nombre ?? 'Clientes Varios' }}</span>
                        <span class="text-gray-500 block">DNI: {{ $venta->cliente->dni ?? '-' }}</span>
                    </div>

                    <div class="text-right">
                        <span class="text-gray-400 font-semibold block uppercase">Fecha y Hora</span>
                        <span class="font-bold text-gray-800">{{ $venta->fecha ? $venta->fecha->format('d/m/Y H:i:s') : '-' }}</span>
                        <span class="text-gray-500 block">Vendedor: {{ $venta->usuario->name ?? 'Vendedor' }}</span>
                    </div>

                    <div>
                        <span class="text-gray-400 font-semibold block uppercase">Tipo de Documento</span>
                        <span class="font-bold text-gray-800 uppercase">{{ $venta->tipo_comprobante }}</span>
                    </div>

                    <div class="text-right">
                        <span class="text-gray-400 font-semibold block uppercase">Estado</span>
                        @if($venta->estado === 'pendiente')
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                <x-icon name="clock" class="w-4 h-4 inline-block align-[-2px] me-1" />Pendiente de Cobro
                            </span>
                        @elseif($venta->estado === 'pagada')
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                <x-icon name="check-circle" class="w-4 h-4 inline-block align-[-2px] me-1" />Pagada
                            </span>
                        @else
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-800">
                                {{ ucfirst($venta->estado) }}
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Detalle de Artículos -->
                <div class="p-6">
                    <table class="w-full text-xs text-left">
                        <thead>
                            <tr class="border-b border-gray-200 text-gray-400 uppercase font-semibold">
                                <th class="pb-2">Cant.</th>
                                <th class="pb-2">Descripción del Juguete</th>
                                <th class="pb-2 text-right">P. Unit.</th>
                                <th class="pb-2 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($venta->detalles as $det)
                                <tr>
                                    <td class="py-2.5 font-bold text-gray-800">{{ $det->cantidad }}</td>
                                    <td class="py-2.5 text-gray-800">
                                        <div class="font-semibold">{{ $det->producto->nombre ?? 'Producto' }}</div>
                                        <div class="text-[10px] text-gray-400 font-mono">SKU: {{ $det->producto->sku ?? '-' }}</div>
                                    </td>
                                    <td class="py-2.5 text-right text-gray-600">S/ {{ number_format($det->precio_unitario, 2) }}</td>
                                    <td class="py-2.5 text-right font-bold text-gray-900">S/ {{ number_format($det->subtotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Totales -->
                    <div class="mt-6 pt-4 border-t border-gray-200 space-y-1 text-xs">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal (Base Imponible):</span>
                            <span>S/ {{ number_format($venta->subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>I.G.V. (18%):</span>
                            <span>S/ {{ number_format($venta->igv, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-base font-extrabold text-gray-900 pt-2 border-t border-gray-200">
                            <span>TOTAL A PAGAR:</span>
                            <span class="text-indigo-600">S/ {{ number_format($venta->total, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Acciones según estado -->
                <div class="p-6 bg-gray-50 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-3">
                    @if($venta->estado === 'pendiente' && Auth::user()->tieneRol('cajero', 'administrador'))
                        <a href="{{ route('caja.index') }}"
                            class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg uppercase tracking-wider shadow-sm transition">
                            <x-icon name="credit-card" class="w-4 h-4 inline-block align-[-2px] me-1" />Cobrar en Caja
                        </a>
                    @elseif($venta->estado === 'pagada')
                        <a href="{{ route('caja.comprobante', $venta->id_venta) }}" target="_blank"
                            class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-lg uppercase tracking-wider shadow-sm transition">
                            <x-icon name="printer" class="w-4 h-4 inline-block align-[-2px] me-1" />Ver Comprobante Oficial
                        </a>
                    @else
                        <span class="text-xs text-gray-500 italic">Ticket pendiente de cobro en módulo de Caja.</span>
                    @endif

                    <button type="button" x-data x-on:click="window.print()" class="text-xs text-gray-600 hover:text-gray-900 font-semibold underline">
                        Imprimir copia del Ticket
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
