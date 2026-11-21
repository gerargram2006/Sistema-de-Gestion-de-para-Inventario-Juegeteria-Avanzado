<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <a href="{{ route('abastecimiento.index') }}" class="text-xs font-semibold text-indigo-600 hover:underline">
                    ← Volver a Abastecimiento
                </a>
                <h2 class="text-xl font-semibold text-gray-800">Padrón de proveedores</h2>
                <p class="text-xs text-gray-500">CUS-03: proveedores autorizados para emitir órdenes de compra.</p>
            </div>
            <a href="{{ route('proveedores.create') }}"
                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                Nuevo proveedor
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            <form method="GET" class="flex flex-wrap gap-3">
                <input type="text" name="q" value="{{ $busqueda }}" placeholder="Buscar por razón social o RUC"
                    class="w-72 rounded-md border-gray-300 text-sm">
                <select name="estado" class="rounded-md border-gray-300 text-sm">
                    <option value="">Todos</option>
                    <option value="activos" @selected($estado === 'activos')>Autorizados</option>
                    <option value="inactivos" @selected($estado === 'inactivos')>No autorizados</option>
                </select>
                <button class="rounded-md bg-gray-800 px-4 py-2 text-sm text-white">Filtrar</button>
            </form>

            <div class="overflow-x-auto rounded-lg bg-white shadow">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Razón social</th>
                            <th class="px-4 py-3">RUC</th>
                            <th class="px-4 py-3">Teléfono</th>
                            <th class="px-4 py-3">Correo</th>
                            <th class="px-4 py-3">Órdenes</th>
                            <th class="px-4 py-3">Autorización</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($proveedores as $proveedor)
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $proveedor->razon_social }}</td>
                                <td class="px-4 py-3 font-mono">{{ $proveedor->ruc }}</td>
                                <td class="px-4 py-3">{{ $proveedor->telefono ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $proveedor->email ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $proveedor->compras_count }}</td>
                                <td class="px-4 py-3">
                                    @if ($proveedor->activo)
                                        <span class="rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-800">Autorizado</span>
                                    @else
                                        <span class="rounded-full bg-gray-200 px-2 py-1 text-xs font-semibold text-gray-700">No autorizado</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('proveedores.edit', $proveedor) }}"
                                            class="text-indigo-600 hover:underline">Editar</a>
                                        <form method="POST" action="{{ route('proveedores.estado', $proveedor) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="text-gray-600 hover:underline">
                                                {{ $proveedor->activo ? 'Desautorizar' : 'Autorizar' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                    No hay proveedores registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $proveedores->links() }}
        </div>
    </div>
</x-app-layout>