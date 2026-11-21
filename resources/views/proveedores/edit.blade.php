<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Editar proveedor</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('proveedores.update', $proveedor) }}"
                class="space-y-6 rounded-lg bg-white p-6 shadow">
                @csrf
                @method('PUT')
                @include('proveedores.partials.campos')

                <div class="flex items-center gap-3">
                    <x-primary-button>Actualizar proveedor</x-primary-button>
                    <a href="{{ route('proveedores.index') }}" class="text-sm text-gray-600 hover:underline">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>