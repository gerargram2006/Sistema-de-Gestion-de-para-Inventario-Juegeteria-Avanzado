<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Mundo del Juguete') }} — Sistema de Gestión de Inventario y Ventas</title>
    <meta name="description" content="Sistema de gestión de inventario, punto de venta, caja y abastecimiento para la juguetería Mundo del Juguete.">
    <link rel="icon" type="{{ file_exists(public_path('images/logo.png')) ? 'image/png' : 'image/svg+xml' }}" href="{{ file_exists(public_path('images/logo.png')) ? asset('images/logo.png') : asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col bg-white font-sans text-slate-600 antialiased">

    {{-- ================= Barra superior ================= --}}
    <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/90 backdrop-blur">
        <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between gap-4">
            <a href="/" class="flex items-center gap-2.5 shrink-0">
                <x-application-logo class="w-8 h-8" />
                <span class="text-base font-bold tracking-tight text-slate-900">
                    Mundo del <span class="text-indigo-600">Juguete</span>
                </span>
            </a>

            <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-600">
                <a href="#modulos" class="hover:text-slate-900 transition">Módulos</a>
                <a href="#alcance" class="hover:text-slate-900 transition">Alcance</a>
            </nav>

            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}"
                       class="inline-flex items-center gap-1.5 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800 transition">
                        Ir al panel
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center gap-1.5 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800 transition">
                        Iniciar sesión
                    </a>
                @endauth
            @endif
        </div>
    </header>

    <main class="flex-1">

        {{-- ================= Presentación ================= --}}
        <section class="border-b border-slate-200 bg-slate-50">
            <div class="max-w-6xl mx-auto px-6 py-16 lg:py-24 grid lg:grid-cols-12 gap-12 lg:gap-16 items-center">

                {{-- Texto --}}
                <div class="min-w-0 lg:col-span-6">
                    <p class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-indigo-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-indigo-600"></span>
                        Proyecto integrador · Tecsup 2026
                    </p>

                    <h1 class="mt-5 text-4xl sm:text-5xl font-bold tracking-tight text-slate-900 leading-[1.1]">
                        Control de inventario y ventas para una juguetería
                    </h1>

                    <p class="mt-5 text-lg leading-relaxed text-slate-600">
                        Sistema web que integra el catálogo, el punto de venta, el cobro con emisión de
                        comprobantes y el abastecimiento con proveedores, sobre una sola base de datos y
                        con acceso diferenciado por rol.
                    </p>

                    <div class="mt-8 flex flex-wrap items-center gap-3">
                        @if (Route::has('login'))
                            <a href="{{ auth()->check() ? url('/dashboard') : route('login') }}"
                               class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 transition">
                                {{ auth()->check() ? 'Ir al panel principal' : 'Acceder al sistema' }}
                            </a>
                        @endif
                        <a href="#modulos"
                           class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                            Conocer los módulos
                        </a>
                    </div>

                    <dl class="mt-10 grid grid-cols-3 gap-6 border-t border-slate-200 pt-6 max-w-md">
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Roles</dt>
                            <dd class="mt-1 text-2xl font-bold text-slate-900">4</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Módulos</dt>
                            <dd class="mt-1 text-2xl font-bold text-slate-900">7</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Casos de uso</dt>
                            <dd class="mt-1 text-2xl font-bold text-slate-900">6</dd>
                        </div>
                    </dl>
                </div>

                {{-- Vista previa del sistema --}}
                <div class="min-w-0 lg:col-span-6">
                    <div class="relative">
                        <div class="absolute -inset-3 rounded-3xl bg-indigo-100/50 blur-2xl" aria-hidden="true"></div>

                        <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-900/5">
                            {{-- Barra de la ventana --}}
                            <div class="flex items-center gap-2 border-b border-slate-200 bg-slate-50 px-4 py-3">
                                <span class="h-2.5 w-2.5 rounded-full bg-slate-300"></span>
                                <span class="h-2.5 w-2.5 rounded-full bg-slate-300"></span>
                                <span class="h-2.5 w-2.5 rounded-full bg-slate-300"></span>
                                <span class="ms-2 text-xs font-medium text-slate-500">Panel principal</span>
                            </div>

                            <div class="p-5 space-y-4">
                                {{-- Indicadores --}}
                                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                                    <div class="rounded-xl border border-slate-200 p-3">
                                        <p class="text-[11px] font-medium uppercase tracking-wide text-slate-500">Ventas del día</p>
                                        <p class="mt-1 text-lg font-bold text-slate-900">S/ 1,284.50</p>
                                    </div>
                                    <div class="rounded-xl border border-slate-200 p-3">
                                        <p class="text-[11px] font-medium uppercase tracking-wide text-slate-500">Por cobrar</p>
                                        <p class="mt-1 text-lg font-bold text-slate-900">3</p>
                                    </div>
                                    <div class="col-span-2 rounded-xl border border-amber-200 bg-amber-50 p-3 sm:col-span-1">
                                        <p class="text-[11px] font-medium uppercase tracking-wide text-amber-700">Stock crítico</p>
                                        <p class="mt-1 text-lg font-bold text-amber-900">5</p>
                                    </div>
                                </div>

                                {{-- Inventario --}}
                                <div class="overflow-hidden rounded-xl border border-slate-200">
                                    <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-4 py-2">
                                        <span class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Inventario</span>
                                        <span class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Estado</span>
                                    </div>

                                    <ul class="divide-y divide-slate-100">
                                        @foreach ([
                                            ['sku' => 'JUG-011', 'archivo' => 'JUG-011.webp', 'nombre' => 'LEGO Classic Caja Grande', 'stock' => '8 u.', 'estado' => 'Disponible', 'clase' => 'bg-emerald-50 text-emerald-700 ring-emerald-200'],
                                            ['sku' => 'JUG-002', 'archivo' => 'JUG-002.webp', 'nombre' => 'Jenga Clásico Original', 'stock' => '4 u.', 'estado' => 'Stock bajo', 'clase' => 'bg-amber-50 text-amber-700 ring-amber-200'],
                                            ['sku' => 'JUG-010', 'archivo' => 'JUG-010.webp', 'nombre' => 'Cry Babies Bebé Llorón', 'stock' => '0 u.', 'estado' => 'Agotado', 'clase' => 'bg-rose-50 text-rose-700 ring-rose-200'],
                                        ] as $fila)
                                            <li class="flex items-center gap-3 px-4 py-3">
                                                <x-imagen :ruta="'images/productos/' . $fila['archivo']" texto=""
                                                          :alt="$fila['nombre']" class="h-10 w-10 shrink-0 rounded-lg bg-slate-100" />
                                                <div class="min-w-0 flex-1">
                                                    <p class="truncate text-sm font-semibold text-slate-900">{{ $fila['nombre'] }}</p>
                                                    <p class="text-xs text-slate-500">
                                                        <span class="font-mono">{{ $fila['sku'] }}</span> · {{ $fila['stock'] }}
                                                    </p>
                                                </div>
                                                <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 ring-inset {{ $fila['clase'] }}">
                                                    {{ $fila['estado'] }}
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ================= Módulos ================= --}}
        <section id="modulos" class="max-w-6xl mx-auto px-6 py-16 lg:py-20">
            <div class="max-w-2xl">
                <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">Módulos del sistema</h2>
                <p class="mt-3 text-base leading-relaxed text-slate-600">
                    Cada módulo corresponde a un caso de uso del sistema (CUS) y está disponible solo para
                    los roles autorizados.
                </p>
            </div>

            <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['icono' => 'shopping-cart', 'titulo' => 'Punto de venta', 'codigo' => 'CUS-01', 'rol' => 'Vendedor · Administrador', 'detalle' => 'Registro de la venta con validación de stock, datos del cliente y emisión del ticket.'],
                    ['icono' => 'credit-card', 'titulo' => 'Caja y cobros', 'codigo' => 'CUS-02', 'rol' => 'Cajero · Administrador', 'detalle' => 'Cobro del ticket, cálculo del vuelto y emisión de boleta o factura con correlativo por serie.'],
                    ['icono' => 'exclamation-triangle', 'titulo' => 'Alertas de stock', 'codigo' => 'CUS-03', 'rol' => 'Almacenero · Administrador', 'detalle' => 'Detección de juguetes en nivel de reposición o agotados y sugerencia de órdenes de compra.'],
                    ['icono' => 'truck', 'titulo' => 'Abastecimiento', 'codigo' => 'CUS-04', 'rol' => 'Almacenero · Administrador', 'detalle' => 'Órdenes de compra a proveedores y confirmación del ingreso físico de la mercadería.'],
                ] as $modulo)
                    <article class="flex flex-col rounded-2xl border border-slate-200 bg-white p-6 transition hover:border-slate-300 hover:shadow-sm">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-slate-700">
                            <x-icon :name="$modulo['icono']" class="h-5 w-5" />
                        </div>
                        <h3 class="mt-4 text-base font-semibold text-slate-900">{{ $modulo['titulo'] }}</h3>
                        <p class="mt-2 flex-1 text-sm leading-relaxed text-slate-600">{{ $modulo['detalle'] }}</p>
                        <p class="mt-4 border-t border-slate-100 pt-3 text-xs font-medium text-slate-500">
                            <span class="font-mono text-slate-700">{{ $modulo['codigo'] }}</span> · {{ $modulo['rol'] }}
                        </p>
                    </article>
                @endforeach
            </div>
        </section>

        {{-- ================= Alcance técnico ================= --}}
        <section id="alcance" class="border-t border-slate-200 bg-slate-50">
            <div class="max-w-6xl mx-auto px-6 py-16 lg:py-20">
                <div class="max-w-2xl">
                    <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">Alcance técnico</h2>
                    <p class="mt-3 text-base leading-relaxed text-slate-600">
                        Decisiones de diseño que sostienen la operación diaria de la tienda.
                    </p>
                </div>

                <div class="mt-10 grid gap-8 sm:grid-cols-3">
                    @foreach ([
                        ['icono' => 'lock-closed', 'titulo' => 'Acceso por rol', 'detalle' => 'Las cuentas las crea el administrador. Cada módulo valida el rol del usuario y una cuenta dada de baja pierde el acceso de inmediato.'],
                        ['icono' => 'receipt-percent', 'titulo' => 'Comprobantes trazables', 'detalle' => 'Boletas y facturas se numeran de forma correlativa e independiente por serie, y cada cobro queda asociado a su ticket de venta.'],
                        ['icono' => 'archive-box', 'titulo' => 'Integridad del inventario', 'detalle' => 'Las operaciones de stock se ejecutan en transacciones con bloqueo de filas, lo que evita el doble cobro y las existencias negativas, y están cubiertas por pruebas automatizadas.'],
                    ] as $punto)
                        <div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-indigo-600">
                                <x-icon :name="$punto['icono']" class="h-5 w-5" />
                            </div>
                            <h3 class="mt-4 text-base font-semibold text-slate-900">{{ $punto['titulo'] }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $punto['detalle'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </main>

    {{-- ================= Pie ================= --}}
    <footer class="border-t border-slate-200 bg-white">
        <div class="max-w-6xl mx-auto px-6 py-8 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
            <p>© {{ date('Y') }} Mundo del Juguete S.A.C.</p>
            <p>Ingeniería de Requerimientos y Diseño de Software · Tecsup</p>
        </div>
    </footer>

</body>
</html>
