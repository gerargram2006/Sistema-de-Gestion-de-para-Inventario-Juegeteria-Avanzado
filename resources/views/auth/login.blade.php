<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-5 text-center">
        <h2 class="text-2xl font-extrabold text-gray-900">Bienvenido</h2>
        <p class="text-xs text-gray-500 mt-1">Inicia sesión para acceder al sistema de inventario y ventas</p>
    </div>

    <form method="POST" action="{{ route('login') }}" x-data="{
        fill(email, pass) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = pass;
        }
    }">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Correo Electrónico')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="usuario@mundojuguete.pe" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Contraseña')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" placeholder="••••••••" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Recordar sesión') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-xs text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('¿Olvidaste tu contraseña?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Ingresar') }}
            </x-primary-button>
        </div>

        {{-- Acceso rapido de prueba: solo con APP_ENV=local, nunca en produccion --}}
        @env('local')
        <!-- Acceso Rápido de Credenciales (Pruebas / Demostración) -->
        <div class="mt-6 pt-4 border-t border-gray-100">
            <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2 text-center">
                Acceso Rápido por Rol (Clic para autocompletar)
            </p>
            <div class="grid grid-cols-2 gap-2 text-[11px]">
                <button type="button" @click="fill('admin@mundojuguete.pe', 'Admin123*')"
                    class="p-2 rounded-lg border border-purple-200 bg-purple-50 hover:bg-purple-100 text-purple-900 text-left transition">
                    <span class="font-bold block">Administrador</span>
                    <span class="font-mono text-[10px] text-purple-700">admin@mundojuguete.pe</span>
                </button>
                <button type="button" @click="fill('vendedor@mundojuguete.pe', 'Venta123*')"
                    class="p-2 rounded-lg border border-blue-200 bg-blue-50 hover:bg-blue-100 text-blue-900 text-left transition">
                    <span class="font-bold block">Vendedor (POS)</span>
                    <span class="font-mono text-[10px] text-blue-700">vendedor@mundojuguete.pe</span>
                </button>
                <button type="button" @click="fill('cajero@mundojuguete.pe', 'Caja123*')"
                    class="p-2 rounded-lg border border-emerald-200 bg-emerald-50 hover:bg-emerald-100 text-emerald-900 text-left transition">
                    <span class="font-bold block">Cajero (Caja)</span>
                    <span class="font-mono text-[10px] text-emerald-700">cajero@mundojuguete.pe</span>
                </button>
                <button type="button" @click="fill('almacen@mundojuguete.pe', 'Almacen123*')"
                    class="p-2 rounded-lg border border-amber-200 bg-amber-50 hover:bg-amber-100 text-amber-900 text-left transition">
                    <span class="font-bold block">Almacenero</span>
                    <span class="font-mono text-[10px] text-amber-700">almacen@mundojuguete.pe</span>
                </button>
            </div>
        </div>
        @endenv
    </form>
</x-guest-layout>
