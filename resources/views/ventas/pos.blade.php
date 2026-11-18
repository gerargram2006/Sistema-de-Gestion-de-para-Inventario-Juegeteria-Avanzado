<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                    <x-icon name="shopping-cart" class="w-7 h-7 text-indigo-600" /> {{ __('Punto de Venta (POS) - Mundo del Juguete') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">CUS-01: Generación de ticket y atención inmediata al cliente.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('ventas.index') }}" class="inline-flex items-center px-3.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition">
                    <x-icon name="clipboard-document-list" class="w-4 h-4 inline-block align-[-2px] me-1" />Historial de Tickets
                </a>
                <button type="button" @click="$dispatch('open-modal', 'modal-nuevo-cliente')"
                    class="inline-flex items-center px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                    <x-icon name="plus" class="w-4 h-4 inline-block align-[-2px] me-1" />Nuevo Cliente
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6" x-data="posApp()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Mensajes Flash -->
            @if (session('success'))
                <div class="mb-5 p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-lg shadow-sm">
                    <p class="text-sm text-emerald-800 font-medium">{{ session('success') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg shadow-sm">
                    <p class="text-sm text-red-800 font-semibold mb-1">Error al procesar el ticket:</p>
                    <ul class="list-disc list-inside text-xs text-red-700 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Layout Principal POS: 2 Columnas -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <!-- Columna Izquierda: Catálogo y Búsqueda de Juguetes (7 cols) -->
                <div class="lg:col-span-7 space-y-4">
                    <!-- Barra de Búsqueda Rápida y Categorías -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                        <div class="relative mb-3">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <x-icon name="magnifying-glass" class="w-4 h-4" />
                            </span>
                            <input type="text" x-model="search" placeholder="Buscar juguete por nombre o código SKU..."
                                class="w-full pl-9 pr-4 py-2.5 text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm" />
                        </div>

                        <!-- Filtro de Categoría Rápido -->
                        <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
                            <button type="button" @click="selectedCategory = ''"
                                :class="selectedCategory === '' ? 'bg-indigo-600 text-white font-semibold' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                class="px-3 py-1.5 rounded-full transition whitespace-nowrap">
                                Todos
                            </button>
                            <template x-for="cat in categories" :key="cat">
                                <button type="button" @click="selectedCategory = cat"
                                    :class="selectedCategory === cat ? 'bg-indigo-600 text-white font-semibold' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                    class="px-3 py-1.5 rounded-full transition whitespace-nowrap"
                                    x-text="cat">
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Cuadrícula de Productos -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-h-[640px] overflow-y-auto pr-1">
                        <template x-for="prod in filteredProducts" :key="prod.id_producto">
                            <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                                <div>
                                    <!-- Foto del juguete (public/images/productos/{SKU}.jpg) -->
                                    <div class="h-28 mb-3 rounded-lg overflow-hidden bg-gray-100">
                                        <template x-if="prod.imagen_url">
                                            <img :src="prod.imagen_url" :alt="prod.nombre" loading="lazy" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!prod.imagen_url">
                                            <div class="w-full h-full flex flex-col items-center justify-center gap-1 text-gray-400 border border-dashed border-gray-300 rounded-lg">
                                                <x-icon name="photo" class="w-8 h-8" />
                                                <span class="text-[11px] font-medium">Sin foto</span>
                                            </div>
                                        </template>
                                    </div>
                                    <div class="flex items-start justify-between gap-2 mb-2">
                                        <span class="text-[10px] font-mono px-2 py-0.5 bg-gray-100 text-gray-600 rounded font-bold" x-text="prod.sku"></span>
                                        <span class="text-[11px] px-2 py-0.5 rounded-full font-semibold"
                                            :class="prod.stock_actual <= prod.stock_minimo ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800'"
                                            x-text="'Stock: ' + prod.stock_actual">
                                        </span>
                                    </div>
                                    <h4 class="font-bold text-gray-900 text-sm leading-snug" x-text="prod.nombre"></h4>
                                    <p class="text-xs text-gray-500 mt-0.5" x-text="prod.categoria ? prod.categoria.nombre : 'General'"></p>
                                </div>

                                <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between">
                                    <div class="text-base font-extrabold text-indigo-600">
                                        S/ <span x-text="parseFloat(prod.precio).toFixed(2)"></span>
                                    </div>
                                    <button type="button" @click="addToCart(prod)"
                                        :disabled="prod.stock_actual <= 0"
                                        class="inline-flex items-center px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed text-white text-xs font-semibold rounded-lg shadow-sm transition">
                                        + Agregar
                                    </button>
                                </div>
                            </div>
                        </template>

                        <div x-show="filteredProducts.length === 0" class="col-span-2 py-12 text-center text-gray-500 bg-white rounded-xl border border-gray-100">
                            <div class="flex justify-center mb-2"><x-icon name="cube" class="w-10 h-10 text-gray-300" /></div>
                            <p class="font-semibold text-gray-700">No se encontraron juguetes coincidentes</p>
                            <p class="text-xs text-gray-400 mt-1">Verifica el nombre o categoría seleccionada.</p>
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha: Detalle de Venta y Carrito (5 cols) -->
                <div class="lg:col-span-5">
                    <form method="POST" action="{{ route('ventas.store') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-4">
                        @csrf

                        <!-- Cabecera del Ticket -->
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                            <div>
                                <h3 class="font-bold text-gray-900 text-base">Ticket de Venta</h3>
                                <p class="text-xs text-gray-500">Vendedor: {{ Auth::user()->name }}</p>
                            </div>
                            <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                                Estado: Pendiente
                            </span>
                        </div>

                        <!-- Selector de Cliente -->
                        <div>
                            <label for="id_cliente" class="block text-xs font-semibold text-gray-700 uppercase mb-1">Cliente</label>
                            <select id="id_cliente" name="id_cliente" x-model="selectedClient" required
                                class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                                @foreach ($clientes as $cli)
                                    <option value="{{ $cli->id_cliente }}">
                                        {{ $cli->nombre }} (DNI: {{ $cli->dni }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tipo de Comprobante -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Tipo de Comprobante</label>
                            <div class="grid grid-cols-3 gap-2">
                                <label class="flex items-center justify-center p-2 text-xs font-semibold rounded-lg border cursor-pointer transition text-center"
                                    :class="tipoComprobante === 'ticket' ? 'bg-indigo-50 border-indigo-600 text-indigo-700' : 'border-gray-200 text-gray-600 hover:bg-gray-50'">
                                    <input type="radio" name="tipo_comprobante" value="ticket" x-model="tipoComprobante" class="sr-only" />
                                    Ticket
                                </label>
                                <label class="flex items-center justify-center p-2 text-xs font-semibold rounded-lg border cursor-pointer transition text-center"
                                    :class="tipoComprobante === 'boleta' ? 'bg-indigo-50 border-indigo-600 text-indigo-700' : 'border-gray-200 text-gray-600 hover:bg-gray-50'">
                                    <input type="radio" name="tipo_comprobante" value="boleta" x-model="tipoComprobante" class="sr-only" />
                                    Boleta
                                </label>
                                <label class="flex items-center justify-center p-2 text-xs font-semibold rounded-lg border cursor-pointer transition text-center"
                                    :class="tipoComprobante === 'factura' ? 'bg-indigo-50 border-indigo-600 text-indigo-700' : 'border-gray-200 text-gray-600 hover:bg-gray-50'">
                                    <input type="radio" name="tipo_comprobante" value="factura" x-model="tipoComprobante" class="sr-only" />
                                    Factura
                                </label>
                            </div>
                        </div>

                        <!-- Lista de Artículos en el Carrito -->
                        <div class="pt-2">
                            <div class="flex items-center justify-between text-xs font-semibold text-gray-500 uppercase mb-2">
                                <span>Artículos (<span x-text="cart.length"></span>)</span>
                                <button type="button" @click="clearCart()" x-show="cart.length > 0" class="text-rose-600 hover:underline">
                                    Vaciar
                                </button>
                            </div>

                            <div class="space-y-2 max-h-[280px] overflow-y-auto pr-1">
                                <template x-for="(item, index) in cart" :key="item.id_producto">
                                    <div class="p-2.5 rounded-lg border border-gray-200 bg-gray-50/60 flex items-center justify-between gap-3 text-sm">
                                        <!-- Inputs ocultos para enviar al backend -->
                                        <input type="hidden" :name="'items[' + index + '][id_producto]'" :value="item.id_producto">
                                        <input type="hidden" :name="'items[' + index + '][cantidad]'" :value="item.cantidad">

                                        <div class="flex-1 min-w-0">
                                            <div class="font-semibold text-gray-900 truncate text-xs" x-text="item.nombre"></div>
                                            <div class="text-[11px] text-gray-500">
                                                S/ <span x-text="parseFloat(item.precio).toFixed(2)"></span> c/u
                                            </div>
                                        </div>

                                        <!-- Controles de Cantidad -->
                                        <div class="flex items-center gap-1.5">
                                            <button type="button" @click="decreaseQty(item)" class="w-6 h-6 rounded bg-white border border-gray-300 hover:bg-gray-100 flex items-center justify-center text-xs font-bold text-gray-700">
                                                -
                                            </button>
                                            <span class="w-7 text-center font-bold text-xs" x-text="item.cantidad"></span>
                                            <button type="button" @click="increaseQty(item)" :disabled="item.cantidad >= item.stock_actual" class="w-6 h-6 rounded bg-white border border-gray-300 hover:bg-gray-100 disabled:opacity-40 flex items-center justify-center text-xs font-bold text-gray-700">
                                                +
                                            </button>
                                        </div>

                                        <!-- Subtotal Línea -->
                                        <div class="w-16 text-right font-bold text-xs text-gray-900">
                                            S/ <span x-text="(item.cantidad * item.precio).toFixed(2)"></span>
                                        </div>

                                        <!-- Eliminar -->
                                        <button type="button" @click="removeFromCart(item)" class="text-gray-400 hover:text-rose-600 transition">
                                            <x-icon name="x-mark" class="w-4 h-4" />
                                        </button>
                                    </div>
                                </template>

                                <div x-show="cart.length === 0" class="py-8 text-center text-gray-400 text-xs border border-dashed border-gray-200 rounded-lg">
                                    El carrito está vacío.<br>Agrega juguetes desde el catálogo izquierdo.
                                </div>
                            </div>
                        </div>

                        <!-- Resumen Financiero -->
                        <div class="pt-3 border-t border-gray-200 space-y-1.5 text-xs">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal (Base Imponible):</span>
                                <span class="font-semibold">S/ <span x-text="subtotal.toFixed(2)"></span></span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>I.G.V. (18%):</span>
                                <span class="font-semibold">S/ <span x-text="igv.toFixed(2)"></span></span>
                            </div>
                            <div class="flex justify-between text-base font-extrabold text-gray-900 pt-2 border-t border-gray-200">
                                <span>Total a Pagar:</span>
                                <span class="text-indigo-600">S/ <span x-text="total.toFixed(2)"></span></span>
                            </div>
                        </div>

                        <!-- Botón de Envío -->
                        <div class="pt-2">
                            <button type="submit" :disabled="cart.length === 0"
                                class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold text-sm rounded-xl shadow-md transition flex items-center justify-center gap-2">
                                <x-icon name="receipt-percent" class="w-7 h-7 text-indigo-600" /> Generar Ticket de Venta
                            </button>
                            <p class="text-[11px] text-gray-400 text-center mt-2">
                                El ticket se enviará a Caja para su cobro y emisión oficial.
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Registro Rápido de Nuevo Cliente -->
    <x-modal name="modal-nuevo-cliente" :show="false" focusable>
        <form method="POST" action="{{ route('ventas.cliente.store') }}" class="p-6">
            @csrf
            <h3 class="text-lg font-bold text-gray-900 mb-2">
                Registrar Nuevo Cliente
            </h3>
            <p class="text-xs text-gray-500 mb-5">Ingresa los datos para registrar al cliente y asociarlo a compras.</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-left">
                <div>
                    <x-input-label for="cli_dni" :value="__('DNI (8 dígitos)')" />
                    <x-text-input id="cli_dni" name="dni" type="text" maxlength="8" placeholder="Ej: 71234567" class="mt-1 block w-full font-mono text-sm" required />
                </div>

                <div>
                    <x-input-label for="cli_telefono" :value="__('Teléfono / Celular')" />
                    <x-text-input id="cli_telefono" name="telefono" type="text" placeholder="Ej: 987654321" class="mt-1 block w-full text-sm" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="cli_nombre" :value="__('Nombres y Apellidos / Razón Social')" />
                    <x-text-input id="cli_nombre" name="nombre" type="text" placeholder="Ej: Carlos Mendoza Ríos" class="mt-1 block w-full text-sm" required />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="cli_email" :value="__('Correo Electrónico (Opcional)')" />
                    <x-text-input id="cli_email" name="email" type="email" placeholder="ejemplo@correo.com" class="mt-1 block w-full text-sm" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="cli_direccion" :value="__('Dirección (Opcional)')" />
                    <x-text-input id="cli_direccion" name="direccion" type="text" placeholder="Av. Principal 123" class="mt-1 block w-full text-sm" />
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Cancelar
                </x-secondary-button>
                <x-primary-button>
                    Guardar Cliente
                </x-primary-button>
            </div>
        </form>
    </x-modal>

    <!-- Script de Alpine.js para la interactividad del POS -->
    <script>
        function posApp() {
            const rawProducts = @json($productos);
            const categoriesList = [...new Set(rawProducts.map(p => p.categoria ? p.categoria.nombre : 'General'))];

            return {
                products: rawProducts,
                categories: categoriesList,
                search: '',
                selectedCategory: '',
                selectedClient: '{{ $clientes->first()->id_cliente ?? "" }}',
                tipoComprobante: 'boleta',
                cart: [],

                get filteredProducts() {
                    return this.products.filter(p => {
                        const matchesSearch = !this.search ||
                            p.nombre.toLowerCase().includes(this.search.toLowerCase()) ||
                            p.sku.toLowerCase().includes(this.search.toLowerCase());
                        const categoryName = p.categoria ? p.categoria.nombre : 'General';
                        const matchesCategory = !this.selectedCategory || categoryName === this.selectedCategory;
                        return matchesSearch && matchesCategory;
                    });
                },

                addToCart(product) {
                    const existing = this.cart.find(i => i.id_producto === product.id_producto);
                    if (existing) {
                        if (existing.cantidad < product.stock_actual) {
                            existing.cantidad++;
                        } else {
                            alert('No hay más stock disponible para este juguete.');
                        }
                    } else {
                        this.cart.push({
                            id_producto: product.id_producto,
                            sku: product.sku,
                            nombre: product.nombre,
                            precio: parseFloat(product.precio),
                            stock_actual: product.stock_actual,
                            cantidad: 1
                        });
                    }
                },

                increaseQty(item) {
                    if (item.cantidad < item.stock_actual) {
                        item.cantidad++;
                    }
                },

                decreaseQty(item) {
                    if (item.cantidad > 1) {
                        item.cantidad--;
                    } else {
                        this.removeFromCart(item);
                    }
                },

                removeFromCart(item) {
                    this.cart = this.cart.filter(i => i.id_producto !== item.id_producto);
                },

                clearCart() {
                    this.cart = [];
                },

                get total() {
                    return this.cart.reduce((sum, item) => sum + (item.cantidad * item.precio), 0);
                },

                get subtotal() {
                    return this.total / 1.18;
                },

                get igv() {
                    return this.total - this.subtotal;
                }
            };
        }
    </script>
</x-app-layout>
