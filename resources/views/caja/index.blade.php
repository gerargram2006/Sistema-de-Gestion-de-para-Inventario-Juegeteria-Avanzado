<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                    <x-icon name="credit-card" class="w-7 h-7 text-indigo-600" /> {{ __('Módulo de Caja y Cobros') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">CUS-02: Cobro de tickets pendientes, descuento de inventario y emisión de comprobantes fiscales.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                    {{ $ticketsPendientes->count() }} por cobrar
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Mensajes Flash -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-lg shadow-sm">
                    <p class="text-sm text-emerald-800 font-medium">{{ session('success') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg shadow-sm">
                    <p class="text-sm text-red-800 font-semibold mb-1">Error al procesar el cobro:</p>
                    <ul class="list-disc list-inside text-xs text-red-700 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Buscador de Tickets Pendientes -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <form method="GET" action="{{ route('caja.index') }}" class="flex flex-col sm:flex-row gap-3 items-center">
                    <div class="flex-1 relative w-full">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <x-icon name="magnifying-glass" class="w-4 h-4" />
                        </span>
                        <input type="text" name="buscar" value="{{ $buscar }}"
                            placeholder="Buscar ticket por número (TK-...) o cliente..."
                            class="w-full pl-9 pr-4 py-2 text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm" />
                    </div>
                    <button type="submit" class="w-full sm:w-auto px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
                        Buscar Ticket
                    </button>
                    @if($buscar)
                        <a href="{{ route('caja.index') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition">
                            Limpiar
                        </a>
                    @endif
                </form>
            </div>

            <!-- Listado de Tickets Pendientes -->
            <div class="space-y-4">
                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <x-icon name="clock" class="w-7 h-7 text-indigo-600" /> Tickets Pendientes de Cobro
                </h3>

                @if($ticketsPendientes->isEmpty())
                    <div class="bg-white rounded-xl border border-gray-100 p-12 text-center text-gray-500 shadow-sm">
                        <div class="flex justify-center mb-2"><x-icon name="check-circle" class="w-10 h-10 text-gray-300" /></div>
                        <p class="font-bold text-gray-800 text-base">¡No hay tickets pendientes en cola!</p>
                        <p class="text-xs text-gray-400 mt-1">Todos los tickets generados por los vendedores han sido cobrados o no se han emitido nuevos tickets aún.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                        @foreach ($ticketsPendientes as $ticket)
                            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm hover:shadow-md transition flex flex-col justify-between"
                                x-data="{
                                    total: {{ (float)$ticket->total }},
                                    metodo: 'efectivo',
                                    montoRecibido: {{ (float)$ticket->total }},
                                    get vuelto() {
                                        return Math.max(0, this.montoRecibido - this.total);
                                    }
                                }">
                                <div>
                                    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                                        <span class="font-mono font-bold text-xs bg-indigo-50 text-indigo-700 px-2.5 py-1 rounded-md">
                                            {{ $ticket->numero_ticket }}
                                        </span>
                                        <span class="text-xs text-gray-400">
                                            {{ $ticket->fecha ? $ticket->fecha->format('H:i') : '' }}
                                        </span>
                                    </div>

                                    <div class="mt-3 space-y-1 text-xs">
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Cliente:</span>
                                            <span class="font-bold text-gray-800">{{ $ticket->cliente->nombre ?? 'Clientes Varios' }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">DNI:</span>
                                            <span class="font-mono text-gray-700">{{ $ticket->cliente->dni ?? '-' }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Vendedor:</span>
                                            <span class="text-gray-700">{{ $ticket->usuario->name ?? 'Usuario' }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Artículos:</span>
                                            <span class="font-semibold text-gray-800">{{ $ticket->detalles->sum('cantidad') }} unidades</span>
                                        </div>
                                    </div>

                                    <!-- Lista reducida de juguetes -->
                                    <div class="mt-3 pt-3 border-t border-gray-100 text-[11px] text-gray-600 space-y-1">
                                        @foreach ($ticket->detalles->take(3) as $d)
                                            <div class="flex justify-between truncate">
                                                <span>{{ $d->cantidad }}x {{ $d->producto->nombre }}</span>
                                                <span class="font-semibold">S/ {{ number_format($d->subtotal, 2) }}</span>
                                            </div>
                                        @endforeach
                                        @if($ticket->detalles->count() > 3)
                                            <div class="text-[10px] text-gray-400 italic">
                                                + {{ $ticket->detalles->count() - 3 }} artículo(s) más...
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-4 pt-3 border-t border-gray-100">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-xs uppercase font-bold text-gray-500">Total a Cobrar:</span>
                                        <span class="text-lg font-black text-indigo-600">
                                            S/ {{ number_format($ticket->total, 2) }}
                                        </span>
                                    </div>

                                    <button type="button" @click="$dispatch('open-modal', 'modal-cobrar-{{ $ticket->id_venta }}')"
                                        class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg uppercase tracking-wider shadow-sm transition flex items-center justify-center gap-1.5">
                                        <x-icon name="credit-card" class="w-7 h-7 text-indigo-600" /> Cobrar Ticket
                                    </button>

                                    <!-- Modal de Cobro -->
                                    <x-modal name="modal-cobrar-{{ $ticket->id_venta }}" :show="false" focusable>
                                        <form method="POST" action="{{ route('caja.cobrar', $ticket->id_venta) }}" class="p-6">
                                            @csrf
                                            <div class="flex items-center justify-between pb-3 border-b border-gray-200 mb-4">
                                                <h3 class="text-lg font-bold text-gray-900">
                                                    Procesar Cobro: {{ $ticket->numero_ticket }}
                                                </h3>
                                                <span class="font-mono text-xs px-2.5 py-1 rounded bg-gray-100 font-bold uppercase">
                                                    {{ $ticket->tipo_comprobante }}
                                                </span>
                                            </div>

                                            <div class="bg-gray-50 p-4 rounded-xl mb-4 space-y-1.5 text-xs">
                                                <div class="flex justify-between">
                                                    <span class="text-gray-500">Cliente:</span>
                                                    <span class="font-bold text-gray-800">{{ $ticket->cliente->nombre }} (DNI: {{ $ticket->cliente->dni }})</span>
                                                </div>
                                                <div class="flex justify-between text-sm font-extrabold text-gray-900 pt-1 border-t border-gray-200">
                                                    <span>Monto Total a Pagar:</span>
                                                    <span class="text-indigo-600 text-base">S/ {{ number_format($ticket->total, 2) }}</span>
                                                </div>
                                            </div>

                                            <div class="space-y-4 text-left">
                                                <!-- Método de Pago -->
                                                <div>
                                                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Método de Pago</label>
                                                    <select name="metodo_pago" x-model="metodo" class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm" required>
                                                        <option value="efectivo">Efectivo</option>
                                                        <option value="tarjeta">Tarjeta Débito / Crédito</option>
                                                        <option value="yape">Yape</option>
                                                        <option value="plin">Plin</option>
                                                    </select>
                                                </div>

                                                <!-- Monto Recibido y Vuelto (solo en efectivo) -->
                                                <div x-show="metodo === 'efectivo'" x-cloak>
                                                    <x-input-label for="monto_recibido_{{ $ticket->id_venta }}" :value="__('Monto Recibido en Caja (S/.)')" />
                                                    <x-text-input id="monto_recibido_{{ $ticket->id_venta }}" name="monto_recibido"
                                                        type="number" step="0.01" min="{{ $ticket->total }}"
                                                        x-model="montoRecibido"
                                                        class="mt-1 block w-full text-base font-bold text-gray-900"
                                                        x-bind:required="metodo === 'efectivo'" x-bind:disabled="metodo !== 'efectivo'" />
                                                </div>

                                                <div x-show="metodo === 'efectivo'" x-cloak
                                                    class="p-3 bg-indigo-50 border border-indigo-100 rounded-lg flex justify-between items-center text-sm">
                                                    <span class="font-semibold text-indigo-900">Vuelto a entregar:</span>
                                                    <span class="text-lg font-black text-indigo-700">
                                                        S/ <span x-text="vuelto.toFixed(2)"></span>
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="mt-6 flex justify-end gap-3">
                                                <x-secondary-button x-on:click="$dispatch('close')">
                                                    Cancelar
                                                </x-secondary-button>
                                                <button type="submit"
                                                    class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase tracking-widest rounded-lg transition shadow-sm">
                                                    Confirmar Pago y Emitir Comprobante
                                                </button>
                                            </div>
                                        </form>
                                    </x-modal>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Últimas Ventas Cobradas -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-100">
                    <h3 class="font-bold text-base text-gray-900 flex items-center gap-2">
                        <x-icon name="check-circle" class="w-7 h-7 text-indigo-600" /> Comprobantes Emitidos Recientemente
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-gray-50/75 border-b border-gray-200 text-xs uppercase font-semibold text-gray-500 tracking-wider">
                                <th class="py-3 px-4">Comprobante</th>
                                <th class="py-3 px-4">Ticket</th>
                                <th class="py-3 px-4">Cliente</th>
                                <th class="py-3 px-4">Método</th>
                                <th class="py-3 px-4 text-right">Total</th>
                                <th class="py-3 px-4 text-right">Vuelto</th>
                                <th class="py-3 px-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 text-xs">
                            @forelse ($ultimasCobradas as $cob)
                                <tr class="hover:bg-gray-50/60">
                                    <td class="py-3 px-4 font-mono font-bold text-emerald-700">
                                        {{ $cob->numero_comprobante ?? 'COMP-' . $cob->id_venta }}
                                    </td>
                                    <td class="py-3 px-4 font-mono text-gray-500">
                                        {{ $cob->numero_ticket }}
                                    </td>
                                    <td class="py-3 px-4 font-medium text-gray-900">
                                        {{ $cob->cliente->nombre ?? 'Clientes Varios' }}
                                    </td>
                                    <td class="py-3 px-4 capitalize text-gray-700">
                                        {{ $cob->metodo_pago }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-extrabold text-gray-900">
                                        S/ {{ number_format($cob->total, 2) }}
                                    </td>
                                    <td class="py-3 px-4 text-right text-gray-600">
                                        S/ {{ number_format($cob->vuelto, 2) }}
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <a href="{{ route('caja.comprobante', $cob->id_venta) }}" target="_blank"
                                            class="inline-flex items-center px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold rounded text-xs transition">
                                            <x-icon name="printer" class="w-4 h-4 inline-block align-[-2px] me-1" />Ver Comprobante
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-gray-400">
                                        No hay cobros recientes registrados en esta sesión.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
