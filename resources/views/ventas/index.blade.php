<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                    <x-icon name="clipboard-document-list" class="w-7 h-7 text-indigo-600" /> {{ __('Historial de Ventas y Tickets') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">Registro de todos los tickets generados en el Punto de Venta.</p>
            </div>
            @if(Auth::user()->tieneRol('vendedor', 'administrador'))
                <a href="{{ route('ventas.pos') }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-lg uppercase tracking-wider shadow-sm transition">
                    <x-icon name="shopping-cart" class="w-4 h-4 inline-block align-[-2px] me-1" />Ir al POS
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Filtros -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <form method="GET" action="{{ route('ventas.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end">
                    <div class="sm:col-span-4">
                        <label for="fecha" class="block text-xs font-semibold text-gray-600 uppercase mb-1">Filtrar por Fecha</label>
                        <input type="date" id="fecha" name="fecha" value="{{ request('fecha') }}"
                            class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm" />
                    </div>

                    <div class="sm:col-span-4">
                        <label for="estado" class="block text-xs font-semibold text-gray-600 uppercase mb-1">Estado de Pago</label>
                        <select id="estado" name="estado" class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                            <option value="">Todos los estados</option>
                            <option value="pendiente" {{ request('estado') === 'pendiente' ? 'selected' : '' }}>Pendiente de Cobro</option>
                            <option value="pagada" {{ request('estado') === 'pagada' ? 'selected' : '' }}>Pagada / Cobrada</option>
                            <option value="anulada" {{ request('estado') === 'anulada' ? 'selected' : '' }}>Anulada</option>
                        </select>
                    </div>

                    <div class="sm:col-span-4 flex gap-2">
                        <button type="submit" class="w-full py-2 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
                            Filtrar
                        </button>
                        @if(request()->hasAny(['fecha', 'estado']))
                            <a href="{{ route('ventas.index') }}" class="py-2 px-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition" title="Limpiar filtros">
                                <x-icon name="x-mark" class="w-4 h-4" />
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Tabla de Ventas -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/75 border-b border-gray-200 text-xs uppercase font-semibold text-gray-500 tracking-wider">
                                <th class="py-3.5 px-4">Ticket</th>
                                <th class="py-3.5 px-4">Fecha / Hora</th>
                                <th class="py-3.5 px-4">Cliente</th>
                                <th class="py-3.5 px-4">Vendedor</th>
                                <th class="py-3.5 px-4">Comprobante</th>
                                <th class="py-3.5 px-4 text-right">Total</th>
                                <th class="py-3.5 px-4 text-center">Estado</th>
                                <th class="py-3.5 px-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 text-sm">
                            @forelse ($ventas as $v)
                                <tr class="hover:bg-gray-50/60 transition">
                                    <td class="py-3.5 px-4 font-mono font-bold text-indigo-600 text-xs">
                                        {{ $v->numero_ticket }}
                                    </td>
                                    <td class="py-3.5 px-4 text-gray-600 text-xs">
                                        {{ $v->fecha ? $v->fecha->format('d/m/Y H:i') : '-' }}
                                    </td>
                                    <td class="py-3.5 px-4 font-medium text-gray-900">
                                        {{ $v->cliente->nombre ?? 'Clientes Varios' }}
                                        <div class="text-[11px] text-gray-400 font-mono">DNI: {{ $v->cliente->dni ?? '-' }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 text-gray-600 text-xs">
                                        {{ $v->usuario->name ?? 'Usuario' }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold uppercase bg-gray-100 text-gray-700">
                                            {{ $v->tipo_comprobante }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-extrabold text-gray-900">
                                        S/ {{ number_format($v->total, 2) }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        @if($v->estado === 'pendiente')
                                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-800">
                                                <x-icon name="clock" class="w-4 h-4 inline-block align-[-2px] me-1" />Pendiente
                                            </span>
                                        @elseif($v->estado === 'pagada')
                                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800">
                                                <x-icon name="check-circle" class="w-4 h-4 inline-block align-[-2px] me-1" />Pagada
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                                {{ ucfirst($v->estado) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-center space-x-2">
                                        <a href="{{ route('ventas.show', $v->id_venta) }}"
                                            class="inline-flex items-center px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded transition">
                                            Ver Ticket
                                        </a>

                                        @if($v->estado === 'pendiente' && Auth::user()->tieneRol('cajero', 'administrador'))
                                            <a href="{{ route('caja.index') }}"
                                                class="inline-flex items-center px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded transition">
                                                Cobrar
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-12 text-center text-gray-500">
                                        <div class="flex justify-center mb-2"><x-icon name="receipt-percent" class="w-10 h-10 text-gray-300" /></div>
                                        <div class="font-semibold text-gray-700">No se encontraron ventas registradas</div>
                                        <div class="text-xs text-gray-400 mt-1">Crea nuevos tickets desde el módulo Punto de Venta.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-gray-100">
                    {{ $ventas->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
