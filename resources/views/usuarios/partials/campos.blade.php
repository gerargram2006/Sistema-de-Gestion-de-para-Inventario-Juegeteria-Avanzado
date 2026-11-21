{{-- Campos compartidos por los formularios de crear y editar usuario --}}
@php($esCreacion = $usuario === null)
@php($usarOld = $esCreacion && old('_origen') === 'crear_usuario')
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-left">
    <div class="sm:col-span-2">
        <x-input-label for="name_{{ $sufijo }}" :value="__('Nombres y Apellidos')" />
        <x-text-input id="name_{{ $sufijo }}" name="name" type="text" class="mt-1 block w-full" :value="$usarOld ? old('name') : $usuario?->name" required />
    </div>

    <div>
        <x-input-label for="usuario_{{ $sufijo }}" :value="__('Usuario')" />
        <x-text-input id="usuario_{{ $sufijo }}" name="usuario" type="text" placeholder="Ej: cajero2" class="mt-1 block w-full font-mono text-sm" :value="$usarOld ? old('usuario') : $usuario?->usuario" required />
    </div>

    <div>
        <x-input-label for="rol_{{ $sufijo }}" :value="__('Rol')" />
        <select id="rol_{{ $sufijo }}" name="rol" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500" required>
            @foreach (\App\Http\Controllers\UsuarioController::ROLES as $valor => $etiqueta)
                <option value="{{ $valor }}" @selected(($usarOld ? old('rol', 'vendedor') : ($usuario?->rol ?? 'vendedor')) === $valor)>{{ $etiqueta }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <x-input-label for="email_{{ $sufijo }}" :value="__('Correo electrónico')" />
        <x-text-input id="email_{{ $sufijo }}" name="email" type="email" placeholder="usuario@mundojuguete.pe" class="mt-1 block w-full" :value="$usarOld ? old('email') : $usuario?->email" required />
    </div>

    <div>
        <x-input-label for="telefono_{{ $sufijo }}" :value="__('Teléfono / Celular')" />
        <x-text-input id="telefono_{{ $sufijo }}" name="telefono" type="text" maxlength="15" class="mt-1 block w-full" :value="$usarOld ? old('telefono') : $usuario?->telefono" />
    </div>

    <div>
        <x-input-label for="password_{{ $sufijo }}" :value="__('Contraseña')" />
        <x-text-input id="password_{{ $sufijo }}" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" :required="$esCreacion" />
    </div>

    <div>
        <x-input-label for="password_confirmation_{{ $sufijo }}" :value="__('Confirmar contraseña')" />
        <x-text-input id="password_confirmation_{{ $sufijo }}" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" :required="$esCreacion" />
    </div>
</div>
