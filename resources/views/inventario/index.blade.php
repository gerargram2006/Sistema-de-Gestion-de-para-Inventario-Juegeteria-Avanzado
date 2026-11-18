<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                    <span>🧸</span> {{ __('Catálogo e Inventario de Juguetes') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">Control de existencias, precios, categorías y alertas de stock en tiempo real.</p>
            </div>
            @if(Auth::user()->tieneRol('administrador', 'almacenero'))
                <button type="button" @click="$dispatch('open-modal', 'modal-crear-producto')"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Nuevo Juguete
                </button>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Mensajes Flash de Éxito o Error -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-lg shadow-sm">
                    <div class="flex items-center">
                        <span class="text-emerald-500 font-bold mr-2">✓</span>
                        <p class="text-sm text-emerald-800 font-medium">{{ session('success') }}</p>
                    </div>
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

            <!-- Barra de Filtros y Búsqueda -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <form method="GET" action="{{ route('inventario.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end">
                    <!-- Búsqueda por texto / SKU -->
                    <div class="sm:col-span-5">
                        <label for="buscar" class="block text-xs font-semibold text-gray-600 uppercase mb-1">Buscar por Nombre o SKU</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                🔍
                            </span>
                            <input type="text" id="buscar" name="buscar" value="{{ request('buscar') }}"
                                placeholder="Ej: LEGO, Hot Wheels, JUG-..."
                                class="w-full pl-9 pr-4 py-2 text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm" />
                        </div>
                    </div>

                    <!-- Filtro por Categoría -->
                    <div class="sm:col-span-3">
                        <label for="categoria" class="block text-xs font-semibold text-gray-600 uppercase mb-1">Categoría</label>
                        <select id="categoria" name="categoria" class="w-full py-2 text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                            <option value="">Todas las categorías</option>
                            @foreach ($categorias as $cat)
                                <option value="{{ $cat->id_categoria }}" {{ request('categoria') == $cat->id_categoria ? 'selected' : '' }}>
                                    {{ $cat->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro Stock Crítico -->
                    <div class="sm:col-span-2">
                        <label for="filtro_stock" class="block text-xs font-semibold text-gray-600 uppercase mb-1">Estado de Stock</label>
                        <select id="filtro_stock" name="filtro_stock" class="w-full py-2 text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                            <option value="">Todos</option>
                            <option value="critico" {{ request('filtro_stock') == 'critico' ? 'selected' : '' }}>⚠️ Stock Crítico</option>
                        </select>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="sm:col-span-2 flex gap-2">
                        <button type="submit" class="w-full py-2 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
                            Filtrar
                        </button>
                        @if(request()->hasAny(['buscar', 'categoria', 'filtro_stock']))
                            <a href="{{ route('inventario.index') }}" class="py-2 px-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition" title="Limpiar filtros">
                                ✕
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Tabla de Productos -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/75 border-b border-gray-200 text-xs uppercase font-semibold text-gray-500 tracking-wider">
                                <th class="py-3.5 px-4">SKU</th>
                                <th class="py-3.5 px-4">Nombre del Juguete</th>
                                <th class="py-3.5 px-4">Categoría</th>
                                <th class="py-3.5 px-4 text-right">Precio Unitario</th>
                                <th class="py-3.5 px-4 text-center">Stock Actual</th>
                                <th class="py-3.5 px-4 text-center">Stock Mínimo</th>
                                <th class="py-3.5 px-4 text-center">Estado</th>
                                @if(Auth::user()->tieneRol('administrador', 'almacenero'))
                                    <th class="py-3.5 px-4 text-center">Acciones</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 text-sm">
                            @forelse ($productos as $producto)
                                <tr class="hover:bg-gray-50/60 transition {{ $producto->estaInactivo() ? 'bg-gray-50 text-gray-400' : ($producto->tieneStockCritico() ? 'bg-amber-50/40' : '') }}">
                                    <td class="py-3.5 px-4 font-mono text-xs font-bold text-gray-700">
                                        {{ $producto->sku }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-semibold text-gray-900">{{ $producto->nombre }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 text-gray-600">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                                            {{ $producto->categoria->nombre ?? 'Sin categoría' }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-bold text-gray-900">
                                        S/ {{ number_format($producto->precio, 2) }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold
                                            @if($producto->stock_actual == 0) bg-rose-100 text-rose-800
                                            @elseif($producto->tieneStockCritico()) bg-amber-100 text-amber-800
                                            @else bg-emerald-100 text-emerald-800 @endif">
                                            {{ $producto->stock_actual }} un.
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center text-xs text-gray-500">
                                        {{ $producto->stock_minimo }} un.
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        @if ($producto->estaInactivo())
                                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-gray-200 text-gray-600" title="Dado de baja del catálogo">
                                                Inactivo
                                            </span>
                                        @elseif ($producto->stock_actual == 0)
                                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                Agotado
                                            </span>
                                        @elseif ($producto->tieneStockCritico())
                                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-800">
                                                Stock Bajo
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                Disponible
                                            </span>
                                        @endif
                                    </td>
                                    @if(Auth::user()->tieneRol('administrador', 'almacenero'))
                                        <td class="py-3.5 px-4 text-center whitespace-nowrap" x-data="{
                                            id: {{ $producto->id_producto }},
                                            sku: '{{ $producto->sku }}',
                                            nombre: '{{ addslashes($producto->nombre) }}',
                                            id_categoria: '{{ $producto->id_categoria }}',
                                            precio: '{{ $producto->precio }}',
                                            stock_actual: '{{ $producto->stock_actual }}',
                                            stock_minimo: '{{ $producto->stock_minimo }}'
                                        }">
                                            <button type="button"
                                                @click="$dispatch('open-modal', 'modal-editar-' + id)"
                                                class="text-indigo-600 hover:text-indigo-900 font-medium text-xs bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-md transition">
                                                Editar
                                            </button>

                                            <form method="POST" action="{{ route('inventario.estado', $producto) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                @if ($producto->estaInactivo())
                                                    <button type="submit" class="ml-1 text-emerald-700 hover:text-emerald-900 font-medium text-xs bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-md transition">
                                                        Reactivar
                                                    </button>
                                                @else
                                                    <button type="submit" class="ml-1 text-rose-600 hover:text-rose-900 font-medium text-xs bg-rose-50 hover:bg-rose-100 px-3 py-1.5 rounded-md transition">
                                                        Dar de baja
                                                    </button>
                                                @endif
                                            </form>

                                            <!-- Modal Editar Producto -->
                                            <x-modal name="modal-editar-{{ $producto->id_producto }}" :show="false" focusable>
                                                <form method="POST" action="{{ route('inventario.update', $producto->id_producto) }}" class="p-6">
                                                    @csrf
                                                    @method('PUT')
                                                    <h3 class="text-lg font-bold text-gray-900 mb-1">
                                                        Editar Juguete: {{ $producto->nombre }}
                                                    </h3>
                                                    <p class="text-xs text-gray-500 mb-4 font-mono">SKU: {{ $producto->sku }}</p>

                                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-left">
                                                        <div class="sm:col-span-2">
                                                            <x-input-label for="edit_nombre_{{ $producto->id_producto }}" :value="__('Nombre del Juguete')" />
                                                            <x-text-input id="edit_nombre_{{ $producto->id_producto }}" name="nombre" type="text" class="mt-1 block w-full" value="{{ $producto->nombre }}" required />
                                                        </div>

                                                        <div>
                                                            <x-input-label for="edit_categoria_{{ $producto->id_producto }}" :value="__('Categoría')" />
                                                            <select id="edit_categoria_{{ $producto->id_producto }}" name="id_categoria" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                                                                @foreach ($categorias as $cat)
                                                                    <option value="{{ $cat->id_categoria }}" {{ $producto->id_categoria == $cat->id_categoria ? 'selected' : '' }}>
                                                                        {{ $cat->nombre }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div>
                                                            <x-input-label for="edit_precio_{{ $producto->id_producto }}" :value="__('Precio (S/.)')" />
                                                            <x-text-input id="edit_precio_{{ $producto->id_producto }}" name="precio" type="number" step="0.01" min="0.10" class="mt-1 block w-full" value="{{ $producto->precio }}" required />
                                                        </div>

                                                        <div>
                                                            <x-input-label for="edit_stock_{{ $producto->id_producto }}" :value="__('Stock Actual')" />
                                                            <x-text-input id="edit_stock_{{ $producto->id_producto }}" name="stock_actual" type="number" min="0" class="mt-1 block w-full" value="{{ $producto->stock_actual }}" required />
                                                        </div>

                                                        <div>
                                                            <x-input-label for="edit_minimo_{{ $producto->id_producto }}" :value="__('Stock Mínimo (Alerta)')" />
                                                            <x-text-input id="edit_minimo_{{ $producto->id_producto }}" name="stock_minimo" type="number" min="1" class="mt-1 block w-full" value="{{ $producto->stock_minimo }}" required />
                                                        </div>
                                                    </div>

                                                    <div class="mt-6 flex justify-end gap-3">
                                                        <x-secondary-button x-on:click="$dispatch('close')">
                                                            Cancelar
                                                        </x-secondary-button>
                                                        <x-primary-button>
                                                            Guardar Cambios
                                                        </x-primary-button>
                                                    </div>
                                                </form>
                                            </x-modal>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ Auth::user()->tieneRol('administrador', 'almacenero') ? 8 : 7 }}" class="py-12 text-center text-gray-500">
                                        <div class="text-3xl mb-2">🔍</div>
                                        <div class="font-semibold text-gray-700">No se encontraron productos</div>
                                        <div class="text-xs text-gray-400 mt-1">Prueba cambiando los criterios del filtro de búsqueda.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="p-4 border-t border-gray-100">
                    {{ $productos->links() }}
                </div>
            </div>
        </div>
    </div>

    @if(Auth::user()->tieneRol('administrador', 'almacenero'))
        <!-- Modal Nuevo Producto -->
        <x-modal name="modal-crear-producto" :show="false" focusable>
            <form method="POST" action="{{ route('inventario.store') }}" class="p-6">
                @csrf
                <h3 class="text-lg font-bold text-gray-900 mb-2">
                    Registrar Nuevo Juguete en el Inventario
                </h3>
                <p class="text-xs text-gray-500 mb-5">Ingresa los datos del producto para asignarlo al catálogo de Mundo del Juguete.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-left">
                    <div>
                        <x-input-label for="new_sku" :value="__('Código SKU (Único)')" />
                        <x-text-input id="new_sku" name="sku" type="text" placeholder="Ej: JUG-NEW-01" class="mt-1 block w-full font-mono text-sm uppercase" required />
                    </div>

                    <div>
                        <x-input-label for="new_categoria" :value="__('Categoría')" />
                        <select id="new_categoria" name="id_categoria" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                            <option value="">Seleccione una categoría</option>
                            @foreach ($categorias as $cat)
                                <option value="{{ $cat->id_categoria }}">{{ $cat->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <x-input-label for="new_nombre" :value="__('Nombre del Juguete')" />
                        <x-text-input id="new_nombre" name="nombre" type="text" placeholder="Ej: Pista de Autos Eléctrica Turbo" class="mt-1 block w-full" required />
                    </div>

                    <div>
                        <x-input-label for="new_precio" :value="__('Precio de Venta (S/.)')" />
                        <x-text-input id="new_precio" name="precio" type="number" step="0.01" min="0.10" placeholder="0.00" class="mt-1 block w-full" required />
                    </div>

                    <div>
                        <x-input-label for="new_stock" :value="__('Stock Inicial')" />
                        <x-text-input id="new_stock" name="stock_actual" type="number" min="0" placeholder="10" class="mt-1 block w-full" required />
                    </div>

                    <div class="sm:col-span-2">
                        <x-input-label for="new_minimo" :value="__('Stock Mínimo (Umbral para Alerta Crítica)')" />
                        <x-text-input id="new_minimo" name="stock_minimo" type="number" min="1" value="5" class="mt-1 block w-full" required />
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <x-secondary-button x-on:click="$dispatch('close')">
                        Cancelar
                    </x-secondary-button>
                    <x-primary-button>
                        Registrar Juguete
                    </x-primary-button>
                </div>
            </form>
        </x-modal>
    @endif
</x-app-layout>
