<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    {{ __('Panel de Control — Mundo del Juguete') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Sesión iniciada como: <span class="font-semibold text-gray-700">{{ Auth::user()->name }}</span>
                    (<span class="capitalize text-indigo-600 font-medium">{{ Auth::user()->rol }}</span>)
                </p>
            </div>

            <div class="flex space-x-2">
                @if(Auth::user()->tieneRol('vendedor', 'administrador'))
                    <a href="{{ route('ventas.pos') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 transition ease-in-out duration-150 shadow-sm">
                        🛒 Nueva Venta POS
                    </a>
                @endif

                @if(Auth::user()->tieneRol('cajero', 'administrador'))
                    <a href="{{ route('caja.index') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 active:bg-emerald-900 transition ease-in-out duration-150 shadow-sm">
                        💳 Ir a Caja ({{ $ventasPendientes }})
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Alerta Destacada si hay Stock Crítico -->
            @if($productosCriticos > 0)
                <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <span class="text-2xl mr-3">⚠️</span>
                            <div>
                                <h3 class="text-sm font-bold text-red-800">
                                    ¡Alerta de Inventario Crítico! ({{ $productosCriticos }} juguetes)
                                </h3>
                                <p class="text-xs text-red-700 mt-0.5">
                                    Existen productos que han alcanzado o están por debajo de su stock mínimo de reposición.
                                </p>
                            </div>
                        </div>
                        @if(Auth::user()->tieneRol('administrador', 'almacenero'))
                            <a href="{{ route('abastecimiento.index') }}" class="text-xs font-bold text-red-800 hover:text-red-900 underline">
                                Gestionar Abastecimiento &rarr;
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Tarjetas KPI -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Total Juguetes -->
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Catálogo Activo</p>
                        <h4 class="text-3xl font-extrabold text-gray-900 mt-1">{{ $totalProductos }}</h4>
                        <span class="text-xs text-gray-500">modelos registrados</span>
                    </div>
                    <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center text-2xl">
                        🧸
                    </div>
                </div>

                <!-- Stock Físico -->
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Stock Físico</p>
                        <h4 class="text-3xl font-extrabold text-gray-900 mt-1">{{ number_format($stockTotal) }}</h4>
                        <span class="text-xs text-gray-500">unidades en almacén</span>
                    </div>
                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center text-2xl">
                        📦
                    </div>
                </div>

                <!-- Alertas de Stock Crítico -->
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Stock Crítico</p>
                        <h4 class="text-3xl font-extrabold {{ $productosCriticos > 0 ? 'text-red-600' : 'text-gray-900' }} mt-1">
                            {{ $productosCriticos }}
                        </h4>
                        <span class="text-xs text-gray-500">&le; stock mínimo</span>
                    </div>
                    <div class="w-12 h-12 {{ $productosCriticos > 0 ? 'bg-red-50 text-red-600' : 'bg-gray-50 text-gray-400' }} rounded-lg flex items-center justify-center text-2xl">
                        🚨
                    </div>
                </div>

                <!-- Ventas por Cobrar -->
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Ventas por Cobrar</p>
                        <h4 class="text-3xl font-extrabold text-amber-600 mt-1">{{ $ventasPendientes }}</h4>
                        <span class="text-xs text-gray-500">tickets pendientes</span>
                    </div>
                    <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-lg flex items-center justify-center text-2xl">
                        ⏳
                    </div>
                </div>
            </div>

            <!-- Tablas de Resumen Rápido -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- Juguetes con menor stock -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-gray-800 text-base">⚠️ Productos con Menor Stock</h3>
                        <a href="{{ route('inventario.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold">Ver catálogo &rarr;</a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="text-xs text-gray-400 border-b border-gray-100 uppercase">
                                <tr>
                                    <th class="pb-2">Producto</th>
                                    <th class="pb-2">Categoría</th>
                                    <th class="pb-2 text-center">Stock</th>
                                    <th class="pb-2 text-center">Mín.</th>
                                    <th class="pb-2 text-right">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($ultimosCriticos as $prod)
                                    <tr>
                                        <td class="py-2.5 font-medium text-gray-900">{{ $prod->nombre }}</td>
                                        <td class="py-2.5 text-xs text-gray-500">{{ $prod->categoria->nombre ?? '-' }}</td>
                                        <td class="py-2.5 text-center font-bold {{ $prod->stock_actual == 0 ? 'text-red-700' : 'text-amber-600' }}">
                                            {{ $prod->stock_actual }}
                                        </td>
                                        <td class="py-2.5 text-center text-xs text-gray-400">{{ $prod->stock_minimo }}</td>
                                        <td class="py-2.5 text-right">
                                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full
                                                {{ $prod->stock_actual == 0 ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800' }}">
                                                {{ ucfirst($prod->estado) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-4 text-center text-gray-400 text-xs">
                                            Todos los productos cuentan con stock óptimo.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Últimas Ventas Generadas -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-gray-800 text-base">🛒 Últimas Transacciones</h3>
                        @if(Auth::user()->tieneRol('cajero', 'administrador'))
                            <a href="{{ route('caja.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold">Ir a caja &rarr;</a>
                        @endif
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="text-xs text-gray-400 border-b border-gray-100 uppercase">
                                <tr>
                                    <th class="pb-2">Ticket</th>
                                    <th class="pb-2">Cliente</th>
                                    <th class="pb-2">Vendedor</th>
                                    <th class="pb-2 text-right">Total</th>
                                    <th class="pb-2 text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($ultimasVentas as $venta)
                                    <tr>
                                        <td class="py-2.5 font-bold font-mono text-xs text-indigo-600">{{ $venta->numero_ticket }}</td>
                                        <td class="py-2.5 text-xs text-gray-700 font-medium">{{ $venta->cliente->nombre ?? 'General' }}</td>
                                        <td class="py-2.5 text-xs text-gray-500">{{ $venta->usuario->name ?? '-' }}</td>
                                        <td class="py-2.5 text-right font-bold text-gray-900">S/ {{ number_format($venta->total, 2) }}</td>
                                        <td class="py-2.5 text-center">
                                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full
                                                {{ $venta->estado === 'pagada' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                                                {{ ucfirst($venta->estado) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-4 text-center text-gray-400 text-xs">
                                            Aún no se han registrado ventas hoy.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
