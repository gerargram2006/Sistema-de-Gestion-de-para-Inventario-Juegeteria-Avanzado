<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                    <x-icon name="clipboard-document-list" class="w-7 h-7 text-indigo-600" /> {{ __('Control de Inventario y Kardex') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">CUS-05 (RF-12 a RF-16 / RN-04): Auditorías físicas, registro de mermas, trazabilidad Kardex y productos de baja rotación.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('control-inventario.exportar', request()->query()) }}"
                    class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-lg uppercase tracking-wider shadow-sm transition">
                    <x-icon name="archive-box" class="w-4 h-4 mr-1.5" />Exportar CSV (Excel)
                </a>
                <button type="button" x-data @click="window.print()"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold text-xs rounded-lg uppercase tracking-wider shadow-sm transition">
                    <x-icon name="printer" class="w-4 h-4 mr-1.5" />Imprimir
                </button>
                <button type="button" x-data @click="$dispatch('open-modal', 'modal-ajuste-kardex')"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-lg uppercase tracking-wider shadow-sm transition">
                    <x-icon name="plus" class="w-4 h-4 mr-1.5" />Auditoría / Merma
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-lg shadow-sm">
                    <p class="text-sm text-emerald-800 font-medium">{{ session('success') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg shadow-sm">
                    <p class="text-sm text-red-800 font-semibold mb-1">No se pudo completar la operación:</p>
                    <ul class="list-disc list-inside text-xs text-red-700 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- KPIs de Control de Inventario -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
                    <span class="text-xs font-semibold text-gray-500 uppercase">Movimientos Kardex</span>
                    <div class="text-2xl font-extrabold text-gray-900 mt-1">{{ number_format($totalMovimientos) }}</div>
                    <span class="text-xs text-gray-400">registros trazables</span>
                </div>
                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
                    <span class="text-xs font-semibold text-gray-500 uppercase">Auditorías Registradas</span>
                    <div class="text-2xl font-extrabold text-indigo-700 mt-1">{{ number_format($totalAuditorias) }}</div>
                    <span class="text-xs text-gray-400">ajustes de conteo físico</span>
                </div>
                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
                    <span class="text-xs font-semibold text-gray-500 uppercase">Mermas / Roturas</span>
                    <div class="text-2xl font-extrabold text-rose-600 mt-1">{{ number_format($totalMermas) }}</div>
                    <span class="text-xs text-gray-400">unidades dadas de baja</span>
                </div>
                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
                    <span class="text-xs font-semibold text-gray-500 uppercase">Baja Rotación (RN-04)</span>
                    <div class="text-2xl font-extrabold text-amber-600 mt-1">{{ $bajaRotacion->count() }}</div>
                    <span class="text-xs text-gray-400">sin ventas en 6 meses</span>
                </div>
            </div>

            <!-- Filtros de Kardex -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <form method="GET" action="{{ route('control-inventario.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end">
                    <div class="sm:col-span-4">
                        <label for="producto" class="block text-xs font-semibold text-gray-600 uppercase mb-1">Juguete</label>
                        <select id="producto" name="producto" class="w-full py-2 text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Todos los juguetes</option>
                            @foreach ($productos as $p)
                                <option value="{{ $p->id_producto }}" @selected(request('producto') == $p->id_producto)>
                                    {{ $p->sku }} — {{ $p->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-3">
                        <label for="tipo" class="block text-xs font-semibold text-gray-600 uppercase mb-1">Tipo de Movimiento</label>
                        <select id="tipo" name="tipo" class="w-full py-2 text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Todos los tipos</option>
                            @foreach ($tipos as $clave => $etiqueta)
                                <option value="{{ $clave }}" @selected(request('tipo') === $clave)>{{ $etiqueta }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="desde" class="block text-xs font-semibold text-gray-600 uppercase mb-1">Desde</label>
                        <input type="date" id="desde" name="desde" value="{{ request('desde') }}" class="w-full py-2 text-sm border-gray-300 rounded-lg">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="hasta" class="block text-xs font-semibold text-gray-600 uppercase mb-1">Hasta</label>
                        <input type="date" id="hasta" name="hasta" value="{{ request('hasta') }}" class="w-full py-2 text-sm border-gray-300 rounded-lg">
                    </div>
                    <div class="sm:col-span-1 flex gap-1.5">
                        <button type="submit" class="w-full py-2 px-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold transition">
                            Ir
                        </button>
                        @if (request()->hasAny(['producto', 'tipo', 'desde', 'hasta']))
                            <a href="{{ route('control-inventario.index') }}" class="py-2 px-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm transition" title="Limpiar">
                                <x-icon name="x-mark" class="w-4 h-4" />
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Tabla Kardex de Movimientos (RF-16) -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-bold text-gray-900">Kardex de Movimientos de Inventario</h3>
                    <span class="text-xs text-gray-500">Mostrando {{ $movimientos->count() }} de {{ $movimientos->total() }}</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-gray-50/75 border-b border-gray-200 text-xs uppercase font-semibold text-gray-500 tracking-wider">
                                <th class="py-3 px-4">Fecha</th>
                                <th class="py-3 px-4">Juguete</th>
                                <th class="py-3 px-4">Tipo</th>
                                <th class="py-3 px-4 text-center">Variación</th>
                                <th class="py-3 px-4 text-center">Saldo (Ant. → Nuevo)</th>
                                <th class="py-3 px-4">Referencia / Sustento</th>
                                <th class="py-3 px-4">Responsable</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 text-xs">
                            @forelse ($movimientos as $mov)
                                <tr class="hover:bg-gray-50/60">
                                    <td class="py-3 px-4 whitespace-nowrap text-gray-600">{{ $mov->fecha?->format('d/m/Y H:i') }}</td>
                                    <td class="py-3 px-4">
                                        <div class="font-semibold text-gray-900">{{ $mov->producto->nombre ?? '—' }}</div>
                                        <div class="font-mono text-[10px] text-gray-400">{{ $mov->producto->sku ?? '' }}</div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-0.5 rounded-full font-semibold
                                            {{ $mov->esEntrada() ? 'bg-emerald-100 text-emerald-800' : ($mov->tipo === 'merma' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                            {{ $mov->etiquetaTipo() }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center font-mono font-bold {{ $mov->esEntrada() ? 'text-emerald-700' : 'text-rose-700' }}">
                                        {{ $mov->esEntrada() ? '+' : '-' }}{{ $mov->cantidad }}
                                    </td>
                                    <td class="py-3 px-4 text-center font-mono text-gray-700">
                                        {{ $mov->stock_anterior }} → <strong>{{ $mov->stock_nuevo }}</strong>
                                    </td>
                                    <td class="py-3 px-4">
                                        @if ($mov->referencia)
                                            <span class="font-mono font-semibold text-indigo-700 block">{{ $mov->referencia }}</span>
                                        @endif
                                        <span class="text-gray-600">{{ $mov->motivo ?? '—' }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-gray-700">{{ $mov->usuario->name ?? 'Sistema' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-10 text-center text-gray-400">
                                        No se registran movimientos de inventario para los filtros seleccionados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-gray-100">
                    {{ $movimientos->links() }}
                </div>
            </div>

            <!-- Sección RN-04: Productos de Baja Rotación (0 ventas en 6 meses) -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-gray-900">Productos de Baja Rotación (RN-04)</h3>
                        <p class="text-xs text-gray-500">Juguetes activos con existencias físicas sin salidas por venta en los últimos 6 meses (sugeridos para promoción o liquidación).</p>
                    </div>
                    <span class="px-3 py-1 text-xs font-bold rounded-full {{ $bajaRotacion->isEmpty() ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                        {{ $bajaRotacion->count() }} producto(s)
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-gray-50/75 border-b border-gray-200 uppercase font-semibold text-gray-500">
                                <th class="py-3 px-4">SKU</th>
                                <th class="py-3 px-4">Juguete</th>
                                <th class="py-3 px-4">Categoría</th>
                                <th class="py-3 px-4 text-center">Stock Inmovilizado</th>
                                <th class="py-3 px-4 text-right">Valor Inmovilizado</th>
                                <th class="py-3 px-4 text-center">Etiqueta</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($bajaRotacion as $br)
                                <tr class="hover:bg-gray-50/60">
                                    <td class="py-3 px-4 font-mono font-bold text-gray-700">{{ $br->sku }}</td>
                                    <td class="py-3 px-4 font-semibold text-gray-900">{{ $br->nombre }}</td>
                                    <td class="py-3 px-4 text-gray-600">{{ $br->categoria->nombre ?? '—' }}</td>
                                    <td class="py-3 px-4 text-center font-bold">{{ $br->stock_actual }} un.</td>
                                    <td class="py-3 px-4 text-right font-mono font-bold">S/ {{ number_format($br->stock_actual * (float) $br->precio, 2) }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 font-semibold">Baja rotación</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-gray-400">
                                        Todos los juguetes con stock han registrado movimiento de ventas en los últimos 6 meses.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Auditoría Física / Registro de Mermas (RF-13, RF-14, RF-15) -->
    <x-modal name="modal-ajuste-kardex" :show="$errors->any()" focusable>
        <form method="POST" action="{{ route('control-inventario.ajuste') }}" class="p-6 text-left space-y-4"
            x-data="{
                operacion: '{{ old('operacion', 'auditoria') }}',
                productos: @js($productos->map(fn ($p) => ['id' => (string) $p->id_producto, 'stock' => $p->stock_actual, 'minimo' => $p->stock_minimo])->keyBy('id')),
                productoId: '{{ old('id_producto', '') }}',
                stockFisico: '{{ old('stock_fisico', '') }}',
                stockMinimo: '{{ old('stock_minimo', '') }}',
                cargarProducto() {
                    const p = this.productos[this.productoId];
                    if (p) {
                        this.stockFisico = p.stock;
                        this.stockMinimo = p.minimo;
                    }
                }
            }">
            @csrf
            <div>
                <h3 class="text-lg font-bold text-gray-900">Registrar Auditoría Física o Merma (CUS-05)</h3>
                <p class="text-xs text-gray-500 mt-0.5">Toda variación queda asentada en el Kardex con usuario, fecha y motivo.</p>
            </div>

            <div class="grid grid-cols-2 gap-2 text-sm">
                <label class="flex items-center gap-2 border rounded-lg px-3 py-2 cursor-pointer"
                    :class="operacion === 'auditoria' ? 'border-indigo-500 bg-indigo-50 font-semibold text-indigo-800' : 'border-gray-200'">
                    <input type="radio" name="operacion" value="auditoria" x-model="operacion"> Auditoría física (RF-13/15)
                </label>
                <label class="flex items-center gap-2 border rounded-lg px-3 py-2 cursor-pointer"
                    :class="operacion === 'merma' ? 'border-rose-500 bg-rose-50 font-semibold text-rose-800' : 'border-gray-200'">
                    <input type="radio" name="operacion" value="merma" x-model="operacion"> Merma / rotura (RF-14)
                </label>
            </div>

            <div>
                <x-input-label for="adj_producto" value="Juguete" />
                <select id="adj_producto" name="id_producto" x-model="productoId" @change="cargarProducto()"
                    class="mt-1 block w-full text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" required>
                    <option value="">Seleccione un juguete...</option>
                    @foreach ($productos as $p)
                        <option value="{{ $p->id_producto }}">{{ $p->sku }} — {{ $p->nombre }} (Sistema: {{ $p->stock_actual }} un.)</option>
                    @endforeach
                </select>
            </div>

            <div x-show="operacion === 'auditoria'" x-cloak class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="stock_fisico" value="Stock Físico Contado" />
                    <x-text-input id="stock_fisico" name="stock_fisico" type="number" min="0" x-model="stockFisico"
                        class="mt-1 block w-full" x-bind:required="operacion === 'auditoria'" />
                </div>
                <div>
                    <x-input-label for="stock_minimo" value="Recalibrar Stock Mínimo" />
                    <x-text-input id="stock_minimo" name="stock_minimo" type="number" min="1" x-model="stockMinimo"
                        class="mt-1 block w-full" />
                </div>
            </div>

            <div x-show="operacion === 'merma'" x-cloak>
                <x-input-label for="cantidad_merma" value="Unidades a dar de baja por merma / daño" />
                <x-text-input id="cantidad_merma" name="cantidad_merma" type="number" min="1" :value="old('cantidad_merma', 1)"
                    class="mt-1 block w-full" x-bind:required="operacion === 'merma'" />
            </div>

            <div>
                <x-input-label for="adj_motivo" value="Sustento / Observación obligatoria" />
                <textarea id="adj_motivo" name="motivo" rows="2" maxlength="255" required
                    placeholder="Ej: Conteo cíclico mensual / Caja dañada en exhibición"
                    class="mt-1 block w-full text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">{{ old('motivo') }}</textarea>
            </div>

            <div class="pt-2 flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                <x-primary-button>Guardar Registro</x-primary-button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
