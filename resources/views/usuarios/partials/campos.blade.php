{{-- Campos compartidos por los formularios de crear y editar usuario --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-left">
    <div class="sm:col-span-2">
        <x-input-label for="name_{{ $sufijo }}" :value="__('Nombres y Apellidos')" />
        <x-text-input id="name_{{ $sufijo }}" name="name" type="text" class="mt-1 block w-full" :value="$usuario?->name" required />
    </div>

    <div>
        <x-input-label for="usuario_{{ $sufijo }}" :value="__('Usuario')" />
        <x-text-input id="usuario_{{ $sufijo }}" name="usuario" type="text" placeholder="Ej: cajero2" class="mt-1 block w-full font-mono text-sm" :value="$usuario?->usuario" required />
    </div>

    <div>
        <x-input-label for="rol_{{ $sufijo }}" :value="__('Rol')" />
        <select id="rol_{{ $sufijo }}" name="rol" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500" required>
            @foreach (\App\Http\Controllers\UsuarioController::ROLES as $valor => $etiqueta)
                <option value="{{ $valor }}" @selected(($usuario?->rol ?? 'vendedor') === $valor)>{{ $etiqueta }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <x-input-label for="email_{{ $sufijo }}" :value="__('Email')" />
        <x-text-input id="email_{{ $sufijo }}" name="email" type="email" placeholder="usuario@mundojuguete.pe" class="mt-1 block w-full" :value="$usuario?->email" required />
    </div>

    <div>
        <x-input-label for="telefono_{{ $sufijo }}" :value="__('Teléfono / Celular')" />
        <x-text-input id="telefono_{{ $sufijo }}" name="telefono" type="text" maxlength="15" class="mt-1 block w-full" :value="$usuario?->telefono" />
    </div>

    <div>
        <x-input-label for="password_{{ $sufijo }}" :value="__('Password')" />
        <x-text-input id="password_{{ $sufijo }}" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" :required="$usuario === null" />
    </div>

    <div>
        <x-input-label for="password_confirmation_{{ $sufijo }}" :value="__('Confirm Password')" />
        <x-text-input id="password_confirmation_{{ $sufijo }}" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" :required="$usuario === null" />
    </div>
</div>
