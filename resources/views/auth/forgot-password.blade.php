<x-guest-layout>
    <div class="mb-4 text-center">
        <h2 class="text-xl font-extrabold text-gray-900">Recuperar contraseña</h2>
        <p class="text-xs text-gray-500 mt-1">
            ¿Olvidaste tu contraseña? Ingresa tu correo electrónico y generaremos un enlace con token de recuperación para que puedas establecer una nueva clave.
        </p>
    </div>

    @if (config('mail.default') === 'log')
        <div class="mb-4 p-3 bg-indigo-50 border border-indigo-100 rounded-lg text-xs text-indigo-900 flex gap-2">
            <x-icon name="lock-closed" class="w-5 h-5 shrink-0 text-indigo-600" />
            <p>
                El enlace con el token de recuperación se generará y registrará en <span class="font-mono font-semibold">storage/logs/laravel.log</span>.
            </p>
        </div>
    @endif

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" autocomplete="off">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Correo Electrónico')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus placeholder="usuario@mundojuguete.pe" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-5">
            <a href="{{ route('login') }}" class="text-xs text-gray-600 hover:text-gray-900 underline">
                Volver al inicio de sesión
            </a>

            <x-primary-button>
                {{ __('Generar enlace de recuperación') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
