<x-guest-layout>
    <div class="mb-5 text-center">
        <h2 class="text-xl font-extrabold text-gray-900">Restablecer contraseña</h2>
        <p class="text-xs text-gray-500 mt-1">
            Ingresa tu nueva contraseña para completar la recuperación de tu cuenta.
        </p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" autocomplete="off">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Correo Electrónico')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="off" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Nueva contraseña')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
            <p class="text-[11px] text-gray-500 mt-1">
                Mínimo 8 caracteres, con mayúsculas, minúsculas y números.
            </p>
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirmar contraseña')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-6">
            <a href="{{ route('login') }}" class="text-xs text-gray-600 hover:text-gray-900 underline">
                Volver al inicio de sesión
            </a>

            <x-primary-button>
                {{ __('Restablecer contraseña') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
