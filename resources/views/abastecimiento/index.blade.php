<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                    <x-icon name="cube" class="w-7 h-7 text-indigo-600" /> {{ __('Módulo de Abastecimiento y Almacén') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">CUS-03: Monitoreo de stock crítico y CUS-04: Recepción de mercadería en almacén.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if(Auth::user()->esAdministrador())
                    <a href="{{ route('proveedores.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold text-xs rounded-lg uppercase tracking-wider shadow-sm transition">
                        <x-icon name="truck" class="w-4 h-4 inline-block align-[-2px] me-1" />Proveedores
                    </a>
                @endif
                <button type="button" @click="$dispatch('open-modal', 'modal-crear-orden')"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-lg uppercase tracking-wider shadow-sm transition">
                    <x-icon name="plus" class="w-4 h-4 inline-block align-[-2px] me-1" />Nueva Orden de Compra
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-8"
        x-data='abastecimientoApp({
            proveedores: @js($proveedoresActivos),
            proveedorSeleccionado: @js(old("id_proveedor", "")),
            items: @js(old("items", [["id_producto" => "", "cantidad" => 10, "precio_costo" => 25.00]]))
        })'
        @if($errors->hasAny(['id_proveedor', 'items', 'items.*.id_producto', 'items.*.cantidad', 'items.*.precio_costo']))
            x-init="$nextTick(() => $dispatch('open-modal', 'modal-crear-orden'))"
        @endif>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- Mensajes Flash -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-lg shadow-sm">
                    <p class="text-sm text-emerald-800 font-medium">{{ session('success') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg shadow-sm">
                    <p class="text-sm text-red-800 font-semibold mb-1">Por favor corrige los siguientes errores:</p>
                    <ul class="list-disc list-inside text-xs text-red-700 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- SECCIÓN 1: Alertas de Stock Crítico (CUS-03) -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <x-icon name="exclamation-triangle" class="w-7 h-7 text-indigo-600" /> Alertas de Stock Crítico (CUS-03)
                        </h3>
                        <p class="text-xs text-gray-500">Juguetes que han alcanzado o cruzado su umbral mínimo de seguridad.</p>
                    </div>
                    <span class="px-3 py-1 text-xs font-bold rounded-full {{ $criticos->count() > 0 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                        {{ $criticos->count() }} producto(s) en alerta
                    </span>
                </div>

                @if($criticos->isEmpty())
                    <div class="bg-white rounded-xl border border-gray-100 p-8 text-center text-gray-500 shadow-sm">
                        <div class="flex justify-center mb-2"><x-icon name="check-circle" class="w-10 h-10 text-gray-300" /></div>
                        <p class="font-bold text-gray-800">¡Niveles de inventario óptimos!</p>
                        <p class="text-xs text-gray-400 mt-1">Todos los juguetes superan el nivel mínimo de stock de seguridad configurado.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($criticos as $prod)
                            @php
                                $cantidadSugerida = max(10, ($prod->stock_minimo * 2) - $prod->stock_actual);
                            @endphp
                            <div role="button" tabindex="0"
                                @click="precargarOrden({{ $prod->id_producto }}, {{ $cantidadSugerida }})"
                                @keydown.enter.prevent="precargarOrden({{ $prod->id_producto }}, {{ $cantidadSugerida }})"
                                @keydown.space.prevent="precargarOrden({{ $prod->id_producto }}, {{ $cantidadSugerida }})"
                                title="Clic para precargar este juguete en una nueva orden de compra"
                                class="bg-white rounded-xl border-l-4 {{ $prod->stock_actual == 0 ? 'border-l-rose-500' : 'border-l-amber-500' }} border border-gray-200 p-4 shadow-sm hover:shadow-md hover:border-indigo-300 transition cursor-pointer">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <x-producto-imagen :producto="$prod" texto="" class="w-12 h-12 shrink-0" />
                                        <div class="min-w-0">
                                            <span class="text-[10px] font-mono font-bold text-gray-500 uppercase">{{ $prod->sku }}</span>
                                            <h4 class="font-bold text-gray-900 text-sm mt-0.5 truncate">{{ $prod->nombre }}</h4>
                                            <span class="text-[11px] text-gray-400">{{ $prod->categoria->nombre ?? 'Juguetería' }}</span>
                                        </div>
                                    </div>
                                    <span class="px-2 py-0.5 text-[10px] font-extrabold rounded uppercase {{ $prod->stock_actual == 0 ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $prod->stock_actual == 0 ? 'Agotado' : 'Bajo' }}
                                    </span>
                                </div>

                                <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                                    <div>
                                        <span class="text-gray-500 block text-[11px]">Stock Actual / Mínimo:</span>
                                        <span class="font-extrabold text-sm {{ $prod->stock_actual == 0 ? 'text-rose-600' : 'text-amber-700' }}">
                                            {{ $prod->stock_actual }}
                                        </span>
                                        <span class="text-gray-400">/ {{ $prod->stock_minimo }} un.</span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-gray-500 block text-[11px]">Déficit Sugerido:</span>
                                        <span class="font-bold text-indigo-600 text-xs">
                                            +{{ $cantidadSugerida }} un.
                                        </span>
                                        <span class="block text-[10px] text-gray-400 mt-1">Clic para precargar</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- SECCIÓN 2: Órdenes Pendientes de Recepción (CUS-04) -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <x-icon name="truck" class="w-7 h-7 text-indigo-600" /> Recepción de Mercadería en Almacén (CUS-04)
                        </h3>
                        <p class="text-xs text-gray-500">Órdenes de compra emitidas pendientes de confirmación física de ingreso.</p>
                    </div>
                    <span class="px-3 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-800">
                        {{ $ordenesPendientes->count() }} orden(es) por recibir
                    </span>
                </div>

                @if($ordenesPendientes->isEmpty())
                    <div class="bg-white rounded-xl border border-gray-100 p-8 text-center text-gray-500 shadow-sm">
                        <div class="flex justify-center mb-2"><x-icon name="cube" class="w-10 h-10 text-gray-300" /></div>
                        <p class="font-bold text-gray-800">No hay órdenes de compra pendientes de entrega</p>
                        <p class="text-xs text-gray-400 mt-1">Crea una nueva orden de compra cuando requieras reponer stock de proveedores.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        @foreach ($ordenesPendientes as $orden)
                            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm space-y-4">
                                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                                    <div>
                                        <span class="font-mono font-bold text-xs bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded">
                                            {{ $orden->numero_orden }}
                                        </span>
                                        <h4 class="font-bold text-gray-900 text-sm mt-1">Proveedor: {{ $orden->proveedorRegistrado?->razon_social ?? $orden->proveedor }}</h4>
                                    </div>
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-amber-100 text-amber-800">
                                        <x-icon name="clock" class="w-4 h-4 inline-block align-[-2px] me-1" />Esperando Mercadería
                                    </span>
                                </div>

                                <!-- Artículos en la orden -->
                                <div class="space-y-1.5 text-xs text-gray-700">
                                    <div class="font-semibold text-gray-500 uppercase text-[11px] mb-1">Juguetes a recibir:</div>
                                    @foreach ($orden->detalles as $det)
                                        <div class="flex justify-between py-1 border-b border-dashed border-gray-100">
                                            <span>
                                                <strong class="text-indigo-600">+{{ $det->cantidad }} un.</strong>
                                                {{ $det->producto->nombre }}
                                            </span>
                                            <span class="font-mono text-gray-500">Costo: S/ {{ number_format($det->precio_costo, 2) }}</span>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
                                    <div>
                                        <span class="text-[11px] text-gray-400 block">Total de Orden:</span>
                                        <span class="text-base font-black text-gray-900">S/ {{ number_format($orden->total, 2) }}</span>
                                    </div>

                                    <form method="POST" action="{{ route('abastecimiento.recibir', $orden->id_compra) }}">
                                        @csrf
                                        <button type="submit" x-data x-on:click="if (! confirm('¿Confirmas que la mercadería física ingresó completa al almacén? Esto actualizará el stock disponible automáticamente.')) $event.preventDefault()"
                                            class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg uppercase tracking-wider shadow-sm transition">
                                            <x-icon name="check" class="w-4 h-4 inline-block align-[-2px] me-1" />Confirmar Recepción
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- SECCIÓN 3: Historial de Órdenes Recibidas -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-100">
                    <h3 class="font-bold text-base text-gray-900 flex items-center gap-2">
                        <x-icon name="archive-box" class="w-7 h-7 text-indigo-600" /> Historial de Mercadería Recibida
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-gray-50/75 border-b border-gray-200 text-xs uppercase font-semibold text-gray-500 tracking-wider">
                                <th class="py-3 px-4">Orden</th>
                                <th class="py-3 px-4">Proveedor</th>
                                <th class="py-3 px-4">Fecha Recepción</th>
                                <th class="py-3 px-4 text-center">Unidades Ingresadas</th>
                                <th class="py-3 px-4 text-right">Costo Total</th>
                                <th class="py-3 px-4 text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 text-xs">
                            @forelse ($ordenesRecibidas as $rec)
                                <tr class="hover:bg-gray-50/60">
                                    <td class="py-3 px-4 font-mono font-bold text-gray-800">
                                        {{ $rec->numero_orden }}
                                    </td>
                                    <td class="py-3 px-4 font-semibold text-gray-900">
                                        {{ $rec->proveedorRegistrado?->razon_social ?? $rec->proveedor }}
                                    </td>
                                    <td class="py-3 px-4 text-gray-600">
                                        {{ $rec->fecha_recepcion ? $rec->fecha_recepcion->format('d/m/Y H:i') : '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-center font-bold text-emerald-700">
                                        +{{ $rec->detalles->sum('cantidad') }} un.
                                    </td>
                                    <td class="py-3 px-4 text-right font-extrabold text-gray-900">
                                        S/ {{ number_format($rec->total, 2) }}
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800">
                                            Ingresado a Almacén
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-gray-400">
                                        No hay recepciones históricas registradas todavía.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal Crear Orden de Abastecimiento -->
    <x-modal name="modal-crear-orden" :show="false" focusable>
        <form method="POST" action="{{ route('abastecimiento.crear-orden') }}" class="p-6">
            @csrf
            <h3 class="text-lg font-bold text-gray-900 mb-1">
                Emitir Orden de Compra / Abastecimiento
            </h3>
            <p class="text-xs text-gray-500 mb-5">Genera una solicitud formal de reposición de juguetes a proveedores.</p>

            <!-- Proveedor -->
            <div class="mb-4">
                <div class="flex items-end gap-2">
                    <div class="flex-1">
                        <x-input-label for="id_proveedor" :value="__('Proveedor autorizado')" />
                        <select id="id_proveedor" name="id_proveedor" x-model="proveedorId"
                            class="mt-1 block w-full text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" required>
                            <option value="">Seleccione un proveedor autorizado...</option>
                            <template x-for="prov in proveedores" :key="prov.id_proveedor">
                                <option :value="String(prov.id_proveedor)" x-text="prov.razon_social"></option>
                            </template>
                        </select>
                    </div>
                    <button type="button" @click="mostrarNuevoProveedor = ! mostrarNuevoProveedor"
                        class="shrink-0 inline-flex items-center px-3 py-2 bg-indigo-50 border border-indigo-200 rounded-md text-xs font-semibold text-indigo-700 hover:bg-indigo-100"
                        title="Registrar un proveedor sin salir de la orden">
                        <x-icon name="plus" class="w-4 h-4 me-1" />Nuevo
                    </button>
                </div>

                <p x-show="proveedores.length === 0" x-cloak class="text-xs text-amber-700 mt-1">
                    No hay proveedores autorizados. Regístralo con el botón «Nuevo» o desde el padrón de Proveedores.
                </p>

                <!-- Alta rápida de proveedor -->
                <div x-show="mostrarNuevoProveedor" x-cloak x-transition
                    class="mt-3 space-y-2 rounded-lg border border-indigo-200 bg-indigo-50/60 p-3"
                    @keydown.enter.prevent="registrarProveedor()">
                    <p class="text-xs font-bold text-indigo-800">Registrar proveedor autorizado</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <div>
                            <x-input-label for="rapido_ruc" value="RUC" class="text-[11px]" />
                            <x-text-input id="rapido_ruc" x-model="nuevoProveedor.ruc" type="text"
                                inputmode="numeric" maxlength="11" placeholder="11 dígitos"
                                class="mt-1 block w-full text-sm" />
                            <p class="text-[11px] text-rose-600 mt-1" x-show="erroresProveedor.ruc?.[0]"
                                x-text="erroresProveedor.ruc?.[0]"></p>
                        </div>
                        <div>
                            <x-input-label for="rapido_razon_social" value="Razón social" class="text-[11px]" />
                            <x-text-input id="rapido_razon_social" x-model="nuevoProveedor.razon_social"
                                type="text" maxlength="150" placeholder="Ej. Mattel Perú S.A."
                                class="mt-1 block w-full text-sm" />
                            <p class="text-[11px] text-rose-600 mt-1" x-show="erroresProveedor.razon_social?.[0]"
                                x-text="erroresProveedor.razon_social?.[0]"></p>
                        </div>
                    </div>
                    <label class="inline-flex items-center gap-2 text-xs text-gray-700">
                        <input type="checkbox" x-model="nuevoProveedor.activo"
                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        Autorizado para recibir órdenes de compra
                    </label>
                    <p class="text-[11px] text-rose-600" x-show="erroresProveedor.general?.[0]"
                        x-text="erroresProveedor.general?.[0]"></p>
                    <div class="flex justify-end gap-2 pt-1">
                        <button type="button" @click="mostrarNuevoProveedor = false"
                            class="text-xs text-gray-600 hover:underline">Cancelar</button>
                        <button type="button" @click="registrarProveedor()" :disabled="guardandoProveedor"
                            class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700 disabled:opacity-50">
                            <span x-text="guardandoProveedor ? 'Guardando...' : 'Guardar proveedor'"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Detalle de Productos a Comprar -->
            <div class="mb-4 space-y-3">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-semibold text-gray-700 uppercase">Juguetes a Solicitar</label>
                    <button type="button" @click="addItem()" class="text-xs text-indigo-600 font-bold hover:underline">
                        + Agregar otro juguete
                    </button>
                </div>

                <template x-for="(item, index) in items" :key="index">
                    <div class="grid grid-cols-12 gap-2 items-center bg-gray-50 p-2.5 rounded-lg border border-gray-200 text-xs">
                        <div class="col-span-5">
                            <label class="text-[10px] text-gray-500 block">Juguete</label>
                            <select :name="'items[' + index + '][id_producto]'" x-model="item.id_producto" class="w-full text-xs border-gray-300 rounded focus:ring-indigo-500 focus:border-indigo-500" required>
                                <option value="">Seleccione...</option>
                                @foreach ($todosProductos as $p)
                                    <option value="{{ $p->id_producto }}">{{ $p->nombre }} (Stock: {{ $p->stock_actual }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-span-3">
                            <label class="text-[10px] text-gray-500 block">Cant. a pedir</label>
                            <input type="number" :name="'items[' + index + '][cantidad]'" x-model="item.cantidad" min="1" class="w-full text-xs border-gray-300 rounded" required />
                        </div>

                        <div class="col-span-3">
                            <label class="text-[10px] text-gray-500 block">Costo Unit (S/.)</label>
                            <input type="number" step="0.01" :name="'items[' + index + '][precio_costo]'" x-model="item.precio_costo" min="0.01" class="w-full text-xs border-gray-300 rounded" required />
                        </div>

                        <div class="col-span-1 text-center pt-3">
                            <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="text-rose-600 font-bold hover:text-rose-800">
                                <x-icon name="x-mark" class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Cancelar
                </x-secondary-button>
                <x-primary-button>
                    Emitir Orden
                </x-primary-button>
            </div>
        </form>
    </x-modal>

    <script>
        function abastecimientoApp(config = {}) {
            const itemVacio = () => ({ id_producto: '', cantidad: 10, precio_costo: 25.00 });

            const normalizarItem = (item) => ({
                id_producto: item?.id_producto ? String(item.id_producto) : '',
                cantidad: Number(item?.cantidad ?? 10),
                precio_costo: Number(item?.precio_costo ?? 25.00),
            });

            return {
                proveedores: config.proveedores ?? [],
                proveedorId: config.proveedorSeleccionado ? String(config.proveedorSeleccionado) : '',
                items: Array.isArray(config.items) && config.items.length
                    ? config.items.map(normalizarItem)
                    : [itemVacio()],
                mostrarNuevoProveedor: false,
                guardandoProveedor: false,
                erroresProveedor: {},
                nuevoProveedor: { razon_social: '', ruc: '', activo: true },

                precargarOrden(idProducto, cantidad) {
                    this.items = [{
                        id_producto: String(idProducto),
                        cantidad: Number(cantidad),
                        precio_costo: 25.00,
                    }];
                    this.$dispatch('open-modal', 'modal-crear-orden');
                },

                addItem() {
                    this.items.push(itemVacio());
                },

                removeItem(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                    }
                },

                async registrarProveedor() {
                    this.guardandoProveedor = true;
                    this.erroresProveedor = {};

                    try {
                        const respuesta = await fetch(@js(route('abastecimiento.proveedores.rapido')), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify(this.nuevoProveedor),
                        });

                        const datos = await respuesta.json();

                        if (! respuesta.ok) {
                            this.erroresProveedor = datos.errors ?? {
                                general: [datos.message ?? 'No se pudo registrar el proveedor.'],
                            };
                            return;
                        }

                        if (! datos.proveedor.activo) {
                            this.erroresProveedor = {
                                general: ['Proveedor registrado, pero no está autorizado. Actívalo desde el padrón para emitirle órdenes.'],
                            };
                            this.nuevoProveedor = { razon_social: '', ruc: '', activo: true };
                            return;
                        }

                        this.proveedores.push(datos.proveedor);
                        this.proveedorId = String(datos.proveedor.id_proveedor);
                        this.nuevoProveedor = { razon_social: '', ruc: '', activo: true };
                        this.mostrarNuevoProveedor = false;
                    } catch (error) {
                        this.erroresProveedor = {
                            general: ['No se pudo conectar con el servidor. Intenta nuevamente.'],
                        };
                    } finally {
                        this.guardandoProveedor = false;
                    }
                },
            };
        }
    </script>
</x-app-layout>
