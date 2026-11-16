<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mundo del Juguete — Sistema de Gestión de Inventario y Ventas</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-indigo-50 via-white to-pink-50 min-h-screen text-gray-800 font-sans flex flex-col justify-between">

    <!-- Header / Navbar -->
    <header class="max-w-7xl mx-auto w-full px-6 py-6 flex justify-between items-center">
        <div class="flex items-center space-x-2">
            <span class="text-3xl">🧸</span>
            <span class="text-2xl font-black tracking-tight text-indigo-700">Mundo del Juguete</span>
        </div>

        <div>
            @if (Route::has('login'))
                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl shadow-md transition">
                            Ir al Panel Principal &rarr;
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl shadow-md transition">
                            Iniciar Sesión
                        </a>
                    @endauth
                </div>
            @endif
        </div>
    </header>

    <!-- Hero Content -->
    <main class="max-w-6xl mx-auto px-6 py-10 flex-1 flex flex-col items-center justify-center text-center">
        <span class="inline-flex items-center gap-2 px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full text-xs font-bold tracking-wide uppercase mb-4">
            🎓 Ingeniería de Requerimientos — Tecsup 2026
        </span>
        <h1 class="text-4xl sm:text-6xl font-black text-gray-900 tracking-tight max-w-4xl leading-tight">
            Gestión Inteligente de Inventario y Ventas para <span class="text-indigo-600">Jugueterías</span>
        </h1>
        <p class="text-base sm:text-lg text-gray-600 mt-4 max-w-2xl leading-relaxed">
            Plataforma integral con control de existencias en tiempo real, punto de venta (POS), cobros con comprobantes electrónicos y módulo de abastecimiento con alertas de stock crítico.
        </p>

        <!-- Módulos Implementados -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mt-12 w-full text-left">
            <!-- CUS-01: POS -->
            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition">
                <div class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center text-xl mb-3">
                    🛒
                </div>
                <h3 class="font-bold text-gray-900 text-base">Punto de Venta (POS)</h3>
                <p class="text-xs text-gray-500 mt-1">CUS-01: Selección de juguetes con validación de stock, registro de clientes y tickets de venta.</p>
                <span class="mt-3 inline-block text-[11px] font-bold text-indigo-600">Rol: Vendedor / Admin</span>
            </div>

            <!-- CUS-02: Caja -->
            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition">
                <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center text-xl mb-3">
                    💳
                </div>
                <h3 class="font-bold text-gray-900 text-base">Caja y Cobros</h3>
                <p class="text-xs text-gray-500 mt-1">CUS-02: Cobro de tickets pendientes, cálculo de vuelto, deducción física de stock y emisión de boletas/facturas.</p>
                <span class="mt-3 inline-block text-[11px] font-bold text-emerald-600">Rol: Cajero / Admin</span>
            </div>

            <!-- CUS-03: Stock Crítico -->
            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition">
                <div class="w-10 h-10 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center text-xl mb-3">
                    ⚠️
                </div>
                <h3 class="font-bold text-gray-900 text-base">Alertas de Stock</h3>
                <p class="text-xs text-gray-500 mt-1">CUS-03: Detección automática de juguetes en nivel de reposición o agotados con alertas visuales.</p>
                <span class="mt-3 inline-block text-[11px] font-bold text-amber-600">Rol: Almacenero / Admin</span>
            </div>

            <!-- CUS-04: Recepción -->
            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition">
                <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center text-xl mb-3">
                    📦
                </div>
                <h3 class="font-bold text-gray-900 text-base">Recepción Almacén</h3>
                <p class="text-xs text-gray-500 mt-1">CUS-04: Gestión de órdenes de compra a proveedores y confirmación de ingreso físico de mercadería.</p>
                <span class="mt-3 inline-block text-[11px] font-bold text-blue-600">Rol: Almacenero / Admin</span>
            </div>
        </div>

        <!-- Cuentas de Acceso Rápido para Pruebas y Evidencia -->
        <div class="mt-12 bg-white/90 backdrop-blur-sm border border-indigo-100 p-6 rounded-2xl shadow-sm w-full max-w-3xl text-left">
            <h4 class="font-black text-gray-900 text-sm uppercase tracking-wider mb-2 flex items-center gap-2">
                <span>🔐</span> Cuentas Preconfiguradas para Demostración y Pruebas
            </h4>
            <p class="text-xs text-gray-500 mb-4">Contraseña universal para todas las cuentas: <code class="px-2 py-0.5 bg-gray-100 font-bold text-indigo-700 rounded font-mono">password</code></p>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                <div class="p-3 bg-purple-50 border border-purple-200 rounded-xl">
                    <span class="font-bold text-purple-900 block">Administrador</span>
                    <span class="text-gray-600 block text-[11px] font-mono mt-1">admin@jugueteria.com</span>
                    <span class="text-[10px] text-purple-700 block mt-1">Acceso total a todos los módulos</span>
                </div>
                <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl">
                    <span class="font-bold text-blue-900 block">Vendedor</span>
                    <span class="text-gray-600 block text-[11px] font-mono mt-1">vendedor@jugueteria.com</span>
                    <span class="text-[10px] text-blue-700 block mt-1">POS y emisión de tickets</span>
                </div>
                <div class="p-3 bg-green-50 border border-green-200 rounded-xl">
                    <span class="font-bold text-green-900 block">Cajero</span>
                    <span class="text-gray-600 block text-[11px] font-mono mt-1">cajero@jugueteria.com</span>
                    <span class="text-[10px] text-green-700 block mt-1">Caja y cobro de comprobantes</span>
                </div>
                <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl">
                    <span class="font-bold text-amber-900 block">Almacenero</span>
                    <span class="text-gray-600 block text-[11px] font-mono mt-1">almacen@jugueteria.com</span>
                    <span class="text-[10px] text-amber-700 block mt-1">Stock, compras y recepción</span>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-6 text-center text-xs text-gray-400 border-t border-gray-100">
        © 2026 Mundo del Juguete S.A.C. — Entregable de Ingeniería de Requerimientos.
    </footer>

</body>
</html>
