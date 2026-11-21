<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                    <x-icon name="users" class="w-7 h-7 text-indigo-600" /> {{ __('Gestión de Usuarios y Roles') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">Alta de trabajadores, asignación de rol operativo y baja de cuentas sin perder su historial.</p>
            </div>
            <button type="button" x-data @click="$dispatch('open-modal', 'modal-crear-usuario')"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Nuevo Usuario
            </button>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Código de restablecimiento recién generado (se muestra una sola vez) -->
            @if (session('token_generado'))
                @php($generado = session('token_generado'))
                <div class="p-5 bg-indigo-50 border border-indigo-200 rounded-xl shadow-sm"
                    x-data="{ copiado: false }">
                    <div class="flex items-start gap-3">
                        <x-icon name="key" class="w-6 h-6 text-indigo-600 shrink-0" />
                        <div class="flex-1">
                            <p class="text-sm font-bold text-indigo-900">
                                Código de un solo uso para {{ $generado['usuario'] }}
                            </p>
                            <p class="text-xs text-indigo-800 mt-0.5">
                                Entrégueselo en persona. Vence en {{ $generado['minutos'] }} minutos, sirve una sola vez
                                y <span class="font-semibold">no se volverá a mostrar</span>.
                            </p>
                            <div class="mt-3 flex flex-wrap items-center gap-3">
                                <span class="font-mono text-2xl font-extrabold tracking-widest text-indigo-900 bg-white border border-indigo-200 rounded-lg px-4 py-2 select-all">
                                    {{ $generado['codigo'] }}
                                </span>
                                <button type="button"
                                    x-on:click="(() => {
                                        const codigo = @js($generado['codigo']);
                                        if (navigator.clipboard && navigator.clipboard.writeText) {
                                            navigator.clipboard.writeText(codigo).then(() => copiado = true);
                                        } else {
                                            const ta = document.createElement('textarea');
                                            ta.value = codigo;
                                            document.body.appendChild(ta);
                                            ta.select();
                                            document.execCommand('copy');
                                            document.body.removeChild(ta);
                                            copiado = true;
                                        }
                                    })()"
                                    class="text-xs font-semibold text-indigo-700 bg-white border border-indigo-200 hover:bg-indigo-100 px-3 py-2 rounded-lg transition">
                                    <span x-show="! copiado">Copiar código</span>
                                    <span x-show="copiado" x-cloak>Copiado</span>
                                </button>
                            </div>
                            <p class="text-xs text-indigo-700 mt-3">
                                El trabajador lo usa en <span class="font-semibold">{{ route('password.request') }}</span>
                                para definir su nueva contraseña.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

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
                    <p class="text-sm text-red-800 font-semibold mb-1">Por favor corrige los siguientes errores:</p>
                    <ul class="list-disc list-inside text-xs text-red-700 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Filtros -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <form method="GET" action="{{ route('usuarios.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end">
                    <div class="sm:col-span-6">
                        <label for="buscar" class="block text-xs font-semibold text-gray-600 uppercase mb-1">Buscar por nombre, usuario o correo</label>
                        <input type="text" id="buscar" name="buscar" value="{{ request('buscar') }}"
                            placeholder="Ej: Hugo, cajero1, @mundojuguete.pe"
                            class="w-full px-4 py-2 text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm" />
                    </div>

                    <div class="sm:col-span-4">
                        <label for="rol" class="block text-xs font-semibold text-gray-600 uppercase mb-1">Rol</label>
                        <select id="rol" name="rol" class="w-full py-2 text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                            <option value="">Todos los roles</option>
                            @foreach ($roles as $valor => $etiqueta)
                                <option value="{{ $valor }}" @selected(request('rol') === $valor)>{{ $etiqueta }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-2 flex gap-2">
                        <button type="submit" class="w-full py-2 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
                            Filtrar
                        </button>
                        @if(request()->hasAny(['buscar', 'rol']))
                            <a href="{{ route('usuarios.index') }}" class="py-2 px-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition" title="Limpiar filtros"><x-icon name="x-mark" class="w-4 h-4 inline-block align-[-2px] me-1" /></a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Tabla de Usuarios -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/75 border-b border-gray-200 text-xs uppercase font-semibold text-gray-500 tracking-wider">
                                <th class="py-3.5 px-4">Nombre</th>
                                <th class="py-3.5 px-4">Usuario</th>
                                <th class="py-3.5 px-4">Correo</th>
                                <th class="py-3.5 px-4">Teléfono</th>
                                <th class="py-3.5 px-4 text-center">Rol</th>
                                <th class="py-3.5 px-4 text-center">Estado</th>
                                <th class="py-3.5 px-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 text-sm">
                            @forelse ($usuarios as $usuario)
                                <tr class="hover:bg-gray-50/60 transition {{ $usuario->estaActivo() ? '' : 'bg-gray-50 text-gray-400' }}">
                                    <td class="py-3.5 px-4 font-semibold {{ $usuario->estaActivo() ? 'text-gray-900' : '' }}">
                                        {{ $usuario->name }}
                                        @if ($usuario->is(Auth::user()))
                                            <span class="ml-1 text-xs font-normal text-indigo-600">(usted)</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 font-mono text-xs">{{ $usuario->usuario ?? '—' }}</td>
                                    <td class="py-3.5 px-4">{{ $usuario->email }}</td>
                                    <td class="py-3.5 px-4">{{ $usuario->telefono ?? '—' }}</td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full
                                            @if($usuario->rol === 'administrador') bg-purple-100 text-purple-800
                                            @elseif($usuario->rol === 'vendedor') bg-blue-100 text-blue-800
                                            @elseif($usuario->rol === 'cajero') bg-green-100 text-green-800
                                            @elseif($usuario->rol === 'almacenero') bg-amber-100 text-amber-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ $roles[$usuario->rol] ?? ucfirst($usuario->rol) }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        @if ($usuario->estaActivo())
                                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800">Activo</span>
                                        @else
                                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-gray-200 text-gray-600">Inactivo</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <div class="flex justify-center gap-2">
                                            <button type="button" x-data
                                                @click="$dispatch('open-modal', 'modal-editar-usuario-{{ $usuario->id }}')"
                                                class="text-indigo-600 hover:text-indigo-900 font-medium text-xs bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-md transition">
                                                Editar
                                            </button>

                                            @if ($usuario->estaActivo())
                                                <form method="POST" action="{{ route('usuarios.token', $usuario) }}">
                                                    @csrf
                                                    <button type="submit"
                                                        title="Genera un código de un solo uso para que el trabajador cambie su contraseña"
                                                        class="text-amber-700 hover:text-amber-900 font-medium text-xs bg-amber-50 hover:bg-amber-100 px-3 py-1.5 rounded-md transition whitespace-nowrap">
                                                        Generar código
                                                    </button>
                                                </form>
                                            @endif

                                            @unless ($usuario->is(Auth::user()))
                                                <form method="POST" action="{{ route('usuarios.estado', $usuario) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    @if ($usuario->estaActivo())
                                                        <button type="submit" class="text-rose-600 hover:text-rose-900 font-medium text-xs bg-rose-50 hover:bg-rose-100 px-3 py-1.5 rounded-md transition">
                                                            Dar de baja
                                                        </button>
                                                    @else
                                                        <button type="submit" class="text-emerald-700 hover:text-emerald-900 font-medium text-xs bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-md transition">
                                                            Reactivar
                                                        </button>
                                                    @endif
                                                </form>
                                            @endunless
                                        </div>

                                        <!-- Modal Editar Usuario -->
                                        <x-modal name="modal-editar-usuario-{{ $usuario->id }}" :show="false" focusable>
                                            <form method="POST" action="{{ route('usuarios.update', $usuario) }}" class="p-6 text-left">
                                                @csrf
                                                @method('PUT')
                                                <h3 class="text-lg font-bold text-gray-900 mb-1">Editar usuario: {{ $usuario->name }}</h3>
                                                <p class="text-xs text-gray-500 mb-4">Deje la contraseña en blanco para conservar la actual.</p>

                                                @include('usuarios.partials.campos', ['usuario' => $usuario, 'sufijo' => $usuario->id])

                                                <div class="mt-6 flex justify-end gap-3">
                                                    <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                                                    <x-primary-button>Guardar Cambios</x-primary-button>
                                                </div>
                                            </form>
                                        </x-modal>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-gray-500">
                                        <div class="flex justify-center mb-2"><x-icon name="magnifying-glass" class="w-10 h-10 text-gray-300" /></div>
                                        <div class="font-semibold text-gray-700">No se encontraron usuarios</div>
                                        <div class="text-xs text-gray-400 mt-1">Prueba cambiando los criterios del filtro de búsqueda.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-gray-100">
                    {{ $usuarios->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nuevo Usuario -->
    <x-modal name="modal-crear-usuario" :show="$errors->any() && old('_origen') === 'crear_usuario'" focusable>
        <form method="POST" action="{{ route('usuarios.store') }}" class="p-6">
            @csrf
            <input type="hidden" name="_origen" value="crear_usuario">
            <h3 class="text-lg font-bold text-gray-900 mb-2">Registrar Nuevo Usuario</h3>
            <p class="text-xs text-gray-500 mb-5">La cuenta se crea activa con el rol seleccionado.</p>

            @include('usuarios.partials.campos', ['usuario' => null, 'sufijo' => 'nuevo'])

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                <x-primary-button>Crear Usuario</x-primary-button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
