<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                <x-icon name="arrow-path" class="w-7 h-7 text-indigo-600" /> {{ __('Devoluciones y Cambios') }}
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                Busque la venta por su ticket o comprobante. Plazo de atención: {{ $plazoDias }} días desde la compra (RF-19).
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Mensajes Flash de Éxito o Error -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-lg shadow-sm">
                    <div class="flex items-center">
                        <x-icon name="check" class="w-5 h-5 text-emerald-500 me-2" />
                        <p class="text-sm text-emerald-800 font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg shadow-sm">
                    <p class="text-sm text-red-800 font-semibold mb-1">No se pudo registrar la solicitud:</p>
                    <ul class="list-disc list-inside text-xs text-red-700 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Búsqueda de la venta -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <form method="GET" action="{{ route('devoluciones.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end">
                    <div class="sm:col-span-10">
                        <label for="buscar" class="block text-xs font-semibold text-gray-600 uppercase mb-1">Ticket o comprobante de la venta</label>
                        <input type="text" id="buscar" name="buscar" value="{{ $buscar }}" required
                            placeholder="Ej: TK-20261118-000012 o B001-000005"
                            class="w-full px-4 py-2 text-sm font-mono border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm" />
                    </div>
                    <div class="sm:col-span-2">
                        <button type="submit" class="w-full py-2 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
                            Buscar venta
                        </button>
                    </div>
                </form>
            </div>

            @if ($buscar !== '' && ! $venta)
                <div class="p-4 bg-amber-50 border-l-4 border-amber-500 rounded-r-lg text-sm text-amber-800">
                    No se encontró ninguna venta con el ticket o comprobante <span class="font-mono font-semibold">{{ $buscar }}</span>.
                </div>
            @endif

            @if ($venta)
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Datos de la venta -->
                    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-5 border-b border-gray-100 flex flex-wrap justify-between gap-3">
                            <div>
                                <div class="font-mono font-bold text-gray-900">{{ $venta->codigo_venta }}</div>
                                <div class="text-xs text-gray-500">
                                    Comprobante {{ $venta->nro_comprob ?? '—' }} · {{ $venta->fecha->format('d/m/Y H:i') }} ·
                                    Cliente: {{ $venta->cliente->nombres ?? '—' }}
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full
                                    @if($venta->estado === 'pagada') bg-green-100 text-green-800
                                    @elseif($venta->estado === 'devuelta') bg-gray-200 text-gray-700
                                    @else bg-amber-100 text-amber-800 @endif">
                                    {{ ucfirst($venta->estado) }}
                                </span>
                                @if ($fueraDePlazo)
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-rose-100 text-rose-800">Fuera de plazo</span>
                                @endif
                            </div>
                        </div>

                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="bg-gray-50/75 border-b border-gray-200 text-xs uppercase font-semibold text-gray-500 tracking-wider">
                                    <th class="py-3 px-4">Juguete</th>
                                    <th class="py-3 px-4 text-center">Vendidas</th>
                                    <th class="py-3 px-4 text-center">Por devolver</th>
                                    <th class="py-3 px-4 text-right">Precio unit.</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($devolvibles as $detalle)
                                    <tr>
                                        <td class="py-3 px-4">
                                            <div class="font-semibold text-gray-900">{{ $detalle->producto->nombre ?? 'Producto eliminado' }}</div>
                                            <div class="text-xs font-mono text-gray-500">{{ $detalle->producto->sku ?? '' }}</div>
                                        </td>
                                        <td class="py-3 px-4 text-center">{{ $detalle->cantidad }}</td>
                                        <td class="py-3 px-4 text-center font-bold {{ $detalle->por_devolver > 0 ? 'text-gray-900' : 'text-gray-400' }}">{{ $detalle->por_devolver }}</td>
                                        <td class="py-3 px-4 text-right">S/ {{ number_format($detalle->precio_unit, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Formulario de devolución -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <h3 class="font-bold text-gray-900 mb-1">Registrar solicitud</h3>

                        @if ($venta->estado !== 'pagada')
                            <p class="text-sm text-gray-500">Esta venta no admite devoluciones (estado: {{ $venta->estado }}).</p>
                        @elseif ($devolvibles->sum('por_devolver') === 0)
                            <p class="text-sm text-gray-500">Todas las unidades de esta venta ya fueron devueltas o cambiadas.</p>
                        @else
                            <p class="text-xs text-gray-500 mb-4">Verifique el estado del juguete antes de resolver.</p>

                            <form method="POST" action="{{ route('devoluciones.store', $venta) }}" class="space-y-4" x-data="{ decision: '{{ old('decision', $fueraDePlazo ? 'rechazar' : 'aprobar') }}' }">
                                @csrf

                                <div>
                                    <x-input-label for="id_producto" :value="__('Juguete')" />
                                    <select id="id_producto" name="id_producto" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                                        @foreach ($devolvibles->where('por_devolver', '>', 0) as $detalle)
                                            <option value="{{ $detalle->id_producto }}" @selected(old('id_producto') == $detalle->id_producto)>
                                                {{ $detalle->producto->nombre }} (máx. {{ $detalle->por_devolver }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <x-input-label for="cantidad" :value="__('Cantidad')" />
                                        <x-text-input id="cantidad" name="cantidad" type="number" min="1" class="mt-1 block w-full" :value="old('cantidad', 1)" required />
                                    </div>
                                    <div>
                                        <x-input-label for="tipo" :value="__('Solicitud')" />
                                        <select id="tipo" name="tipo" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                            <option value="devolucion" @selected(old('tipo') === 'devolucion')>Devolución</option>
                                            <option value="cambio" @selected(old('tipo') === 'cambio')>Cambio</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <x-input-label :value="__('Resolución')" />
                                    <div class="mt-1 grid grid-cols-2 gap-2 text-sm">
                                        <label class="flex items-center gap-2 border rounded-md px-3 py-2 cursor-pointer" :class="decision === 'aprobar' ? 'border-emerald-500 bg-emerald-50' : 'border-gray-200'">
                                            <input type="radio" name="decision" value="aprobar" x-model="decision" @disabled($fueraDePlazo)> Aprobar
                                        </label>
                                        <label class="flex items-center gap-2 border rounded-md px-3 py-2 cursor-pointer" :class="decision === 'rechazar' ? 'border-rose-500 bg-rose-50' : 'border-gray-200'">
                                            <input type="radio" name="decision" value="rechazar" x-model="decision"> Rechazar
                                        </label>
                                    </div>
                                    @if ($fueraDePlazo)
                                        <p class="text-xs text-rose-600 mt-1">Fuera del plazo de {{ $plazoDias }} días: solo puede registrarse como rechazada.</p>
                                    @endif
                                </div>

                                <div>
                                    <x-input-label for="motivo" :value="__('Motivo')" />
                                    <textarea id="motivo" name="motivo" rows="2" maxlength="255"
                                        placeholder="Ej: juguete con falla de fábrica / daños por mal uso"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                        :required="decision === 'rechazar'">{{ old('motivo') }}</textarea>
                                </div>

                                <x-primary-button class="w-full justify-center">Registrar</x-primary-button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Historial reciente -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-100">
                    <h3 class="font-bold text-gray-900">Últimas devoluciones y cambios</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="bg-gray-50/75 border-b border-gray-200 text-xs uppercase font-semibold text-gray-500 tracking-wider">
                                <th class="py-3 px-4">Fecha</th>
                                <th class="py-3 px-4">Venta</th>
                                <th class="py-3 px-4">Juguete</th>
                                <th class="py-3 px-4 text-center">Cant.</th>
                                <th class="py-3 px-4">Tipo</th>
                                <th class="py-3 px-4">Estado</th>
                                <th class="py-3 px-4">Atendió</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($recientes as $devolucion)
                                <tr>
                                    <td class="py-3 px-4 whitespace-nowrap">{{ $devolucion->fecha->format('d/m/Y H:i') }}</td>
                                    <td class="py-3 px-4 font-mono text-xs">{{ $devolucion->venta->codigo_venta ?? '—' }}</td>
                                    <td class="py-3 px-4">{{ $devolucion->producto->nombre ?? '—' }}</td>
                                    <td class="py-3 px-4 text-center">{{ $devolucion->cantidad }}</td>
                                    <td class="py-3 px-4">{{ $devolucion->tipo === 'cambio' ? 'Cambio' : 'Devolución' }}</td>
                                    <td class="py-3 px-4">
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full
                                            {{ $devolucion->fueAprobada() ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                            {{ ucfirst($devolucion->estado) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">{{ $devolucion->usuario->name ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-10 text-center text-gray-500 text-sm">Aún no se registran devoluciones ni cambios.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
