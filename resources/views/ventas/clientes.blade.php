<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                    <x-icon name="users" class="w-7 h-7 text-indigo-600" /> {{ __('Clientes e Historial de Compras') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">RF-27: Consulta de clientes registrados por DNI y seguimiento de compras realizadas.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('ventas.pos') }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-lg uppercase tracking-wider shadow-sm transition">
                    <x-icon name="shopping-cart" class="w-4 h-4 me-1" />Volver al POS
                </a>
                <a href="{{ route('ventas.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs rounded-lg uppercase tracking-wider transition">
                    Historial de Tickets
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <form method="GET" action="{{ route('ventas.clientes') }}" class="flex flex-col sm:flex-row gap-3 items-center">
                    <div class="flex-1 relative w-full">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <x-icon name="magnifying-glass" class="w-4 h-4" />
                        </span>
                        <input type="text" name="buscar" value="{{ $buscar }}"
                            placeholder="Buscar cliente por DNI o nombre..."
                            class="w-full pl-9 pr-4 py-2 text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm" />
                    </div>
                    <button type="submit" class="w-full sm:w-auto px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
                        Buscar
                    </button>
                    @if ($buscar !== '')
                        <a href="{{ route('ventas.clientes') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition">
                            Limpiar
                        </a>
                    @endif
                </form>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-gray-50/75 border-b border-gray-200 text-xs uppercase font-semibold text-gray-500 tracking-wider">
                                <th class="py-3.5 px-4">DNI</th>
                                <th class="py-3.5 px-4">Cliente</th>
                                <th class="py-3.5 px-4">Contacto</th>
                                <th class="py-3.5 px-4 text-center">Tickets</th>
                                <th class="py-3.5 px-4 text-right">Total Pagado</th>
                                <th class="py-3.5 px-4">Últimas Compras</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($clientes as $cliente)
                                <tr class="hover:bg-gray-50/60">
                                    <td class="py-3.5 px-4 font-mono font-bold text-gray-700 text-xs">{{ $cliente->dni }}</td>
                                    <td class="py-3.5 px-4 font-semibold text-gray-900">{{ $cliente->nombres }}</td>
                                    <td class="py-3.5 px-4 text-xs text-gray-600">
                                        <div>{{ $cliente->telefono ?? '—' }}</div>
                                        <div class="text-gray-400">{{ $cliente->correo ?? '' }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 text-center font-bold text-indigo-700">{{ $cliente->ventas_count }}</td>
                                    <td class="py-3.5 px-4 text-right font-extrabold text-gray-900">
                                        S/ {{ number_format((float) ($cliente->total_comprado ?? 0), 2) }}
                                    </td>
                                    <td class="py-3.5 px-4 text-xs">
                                        @if ($cliente->ventas->isEmpty())
                                            <span class="text-gray-400">Sin compras registradas</span>
                                        @else
                                            <div class="flex flex-wrap gap-1.5">
                                                @foreach ($cliente->ventas as $venta)
                                                    <a href="{{ route('ventas.show', $venta->id_venta) }}"
                                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded border text-[11px] font-mono transition
                                                            {{ $venta->estado === 'pagada' ? 'bg-emerald-50 border-emerald-200 text-emerald-800 hover:bg-emerald-100' : 'bg-gray-50 border-gray-200 text-gray-700 hover:bg-gray-100' }}">
                                                        <span>{{ $venta->codigo_venta }}</span>
                                                        <span class="font-sans font-semibold">S/ {{ number_format($venta->total, 2) }}</span>
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-gray-500">
                                        No se encontraron clientes para el criterio ingresado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-gray-100">
                    {{ $clientes->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
