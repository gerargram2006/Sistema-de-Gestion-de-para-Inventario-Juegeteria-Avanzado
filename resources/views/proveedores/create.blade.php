<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Nuevo proveedor</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('proveedores.store') }}"
                class="space-y-6 rounded-lg bg-white p-6 shadow">
                @csrf
                @include('proveedores.partials.campos')

                <div class="flex items-center gap-3">
                    <x-primary-button>Guardar proveedor</x-primary-button>
                    <a href="{{ route('proveedores.index') }}" class="text-sm text-gray-600 hover:underline">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>