<x-guest-layout>
    <div class="mb-5 text-center">
        <h2 class="text-2xl font-extrabold text-gray-900">Restablecer contraseña</h2>
        <p class="text-sm text-gray-500 mt-1">
            Solicite al administrador un código de un solo uso y escríbalo aquí junto con su nueva contraseña.
        </p>
    </div>

    <div class="mb-5 p-3 bg-indigo-50 border border-indigo-100 rounded-lg text-xs text-indigo-900 flex gap-2">
        <x-icon name="lock-closed" class="w-5 h-5 shrink-0 text-indigo-600" />
        <p>
            El código tiene el formato <span class="font-mono font-semibold">ABCD-EFGH-JKMN</span>,
            vence a los {{ $vigenciaMinutos }} minutos y solo puede usarse una vez.
        </p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.restablecer') }}">
        @csrf

        <div>
            <x-input-label for="usuario" :value="__('Usuario o correo')" />
            <x-text-input id="usuario" class="block mt-1 w-full" type="text" name="usuario"
                :value="old('usuario')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('usuario')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="token" :value="__('Código de un solo uso')" />
            <x-text-input id="token" class="block mt-1 w-full font-mono tracking-widest uppercase" type="text"
                name="token" placeholder="ABCD-EFGH-JKMN" maxlength="40" required autocomplete="off" />
            <x-input-error :messages="$errors->get('token')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Nueva contraseña')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password"
                required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
            <p class="text-[11px] text-gray-500 mt-1">
                Mínimo 8 caracteres, con mayúsculas, minúsculas y números.
            </p>
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirmar contraseña')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password"
                name="password_confirmation" required autocomplete="new-password" />
        </div>

        <div class="flex items-center justify-between mt-6">
            <a href="{{ route('login') }}" class="text-xs text-gray-600 hover:text-gray-900 underline">
                Volver al inicio de sesión
            </a>

            <x-primary-button>
                {{ __('Guardar contraseña') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
