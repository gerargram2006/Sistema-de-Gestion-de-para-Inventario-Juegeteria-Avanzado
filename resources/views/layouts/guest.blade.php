<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Mundo del Juguete') }}</title>
        <link rel="icon" type="{{ file_exists(public_path('images/logo.png')) ? 'image/png' : 'image/svg+xml' }}" href="{{ file_exists(public_path('images/logo.png')) ? asset('images/logo.png') : asset('favicon.svg') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gray-50">
        <div class="min-h-screen grid lg:grid-cols-2">
            <!-- Panel de imagen (public/images/fondos/toy-story.webp); solo en pantallas grandes -->
            <div class="hidden lg:block relative overflow-hidden">
                <x-imagen ruta="images/fondos/toy-story.webp" texto="Imagen de la tienda" alt="Tienda Mundo del Juguete - Toy Story"
                    class="absolute inset-0 w-full h-full object-cover border-0" />
                <div class="absolute inset-x-0 bottom-0 p-10 bg-gradient-to-t from-gray-900/85 via-gray-900/35 to-transparent text-white">
                    <p class="text-2xl font-bold">Mundo del Juguete</p>
                    <p class="text-sm text-white/90 mt-1">Inventario, ventas, caja y abastecimiento en un solo sistema.</p>
                </div>
            </div>

            <!-- Formulario -->
            <div class="flex flex-col justify-center items-center px-6 py-10">
                <a href="/" class="flex items-center gap-3">
                    <x-application-logo class="w-12 h-12" />
                    <span class="text-2xl font-extrabold tracking-tight">Mundo del <span class="text-indigo-600">Juguete</span></span>
                </a>

                <div class="w-full sm:max-w-md mt-8 px-6 py-6 bg-white shadow-sm border border-gray-100 rounded-2xl">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
