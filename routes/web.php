<?php

use App\Http\Controllers\AbastecimientoController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\ControlInventarioController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DevolucionController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas Públicas
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Rutas Autenticadas - Panel Principal
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard General con métricas y KPIs en tiempo real
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Catálogo e Inventario General
    Route::prefix('inventario')->name('inventario.')->group(function () {
        Route::get('/', [InventarioController::class, 'index'])->name('index');

        // Registro y modificación de juguetes y categorías restringidos a Administrador y Almacenero
        Route::middleware('role:administrador,almacenero')->group(function () {
            Route::post('/', [InventarioController::class, 'store'])->name('store');
            Route::put('/{producto}', [InventarioController::class, 'update'])->name('update');
            Route::patch('/{producto}/estado', [InventarioController::class, 'cambiarEstado'])->name('estado');
            Route::post('/categorias', [InventarioController::class, 'storeCategoria'])->name('categorias.store');
            Route::patch('/categorias/{categoria}/estado', [InventarioController::class, 'cambiarEstadoCategoria'])->name('categorias.estado');
        });
    });

    // Control de Inventario, Kardex, Auditorías y Mermas (CUS-05 / RF-12 a RF-16 / RN-04) - Solo Administrador
    Route::middleware('role:administrador')->prefix('control-inventario')->name('control-inventario.')->group(function () {
        Route::get('/', [ControlInventarioController::class, 'index'])->name('index');
        Route::post('/ajuste', [ControlInventarioController::class, 'registrarAjuste'])->name('ajuste');
        Route::get('/exportar', [ControlInventarioController::class, 'exportarCsv'])->name('exportar');
    });

    // Módulo Punto de Venta (POS) y Tickets (CUS-01) - Vendedor y Administrador
    Route::middleware('role:vendedor,administrador')->prefix('ventas')->name('ventas.')->group(function () {
        Route::get('/', [VentaController::class, 'index'])->name('index');
        Route::get('/pos', [VentaController::class, 'pos'])->name('pos');
        Route::get('/clientes', [VentaController::class, 'clientes'])->name('clientes');
        Route::post('/', [VentaController::class, 'store'])->name('store');
        Route::post('/cliente', [VentaController::class, 'storeCliente'])->name('cliente.store');
        Route::patch('/{venta}/anular', [VentaController::class, 'anular'])->name('anular');
        Route::get('/{venta}', [VentaController::class, 'show'])->name('show');
    });

    // Módulo de Caja, Cobros y Comprobantes Fiscales (CUS-02) - Cajero y Administrador
    Route::middleware('role:cajero,administrador')->prefix('caja')->name('caja.')->group(function () {
        Route::get('/', [CajaController::class, 'index'])->name('index');
        Route::post('/{venta}/cobrar', [CajaController::class, 'cobrar'])->name('cobrar');
        Route::get('/{venta}/comprobante', [CajaController::class, 'comprobante'])->name('comprobante');
    });

    // Módulo de Abastecimiento, Alertas de Stock y Recepción (CUS-03, CUS-04) - Almacenero y Administrador
    Route::middleware('role:almacenero,administrador')->prefix('abastecimiento')->name('abastecimiento.')->group(function () {
        Route::get('/', [AbastecimientoController::class, 'index'])->name('index');
        Route::post('/orden', [AbastecimientoController::class, 'crearOrden'])->name('crear-orden');
        Route::post('/orden/{compra}/recibir', [AbastecimientoController::class, 'recibirOrden'])->name('recibir');
        Route::patch('/orden/{compra}/cancelar', [AbastecimientoController::class, 'cancelarOrden'])->name('cancelar');
        Route::get('/orden/{compra}/constancia', [AbastecimientoController::class, 'constancia'])->name('constancia');
        Route::post('/proveedores/rapido', [ProveedorController::class, 'storeRapido'])->name('proveedores.rapido');
    });

    // Módulo de Devoluciones y Cambios (CUS-06) - Vendedor, Cajero y Administrador
    Route::middleware('role:vendedor,cajero,administrador')->prefix('devoluciones')->name('devoluciones.')->group(function () {
        Route::get('/', [DevolucionController::class, 'index'])->name('index');
        Route::post('/{venta}', [DevolucionController::class, 'store'])->name('store');
        Route::get('/{devolucion}/constancia', [DevolucionController::class, 'constancia'])->name('constancia');
    });

    // Gestión de Usuarios y Roles (reemplaza al registro público) - Solo Administrador
    Route::middleware('role:administrador')->prefix('usuarios')->name('usuarios.')->group(function () {
        Route::get('/', [UsuarioController::class, 'index'])->name('index');
        Route::post('/', [UsuarioController::class, 'store'])->name('store');
        Route::put('/{usuario}', [UsuarioController::class, 'update'])->name('update');
        Route::patch('/{usuario}/estado', [UsuarioController::class, 'cambiarEstado'])->name('estado');
        Route::post('/{usuario}/token', [UsuarioController::class, 'generarToken'])->name('token');
    });

    // Perfil de Usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Proveedores (CUS-03) - Solo Administrador
    Route::middleware('role:administrador')->prefix('proveedores')->name('proveedores.')->group(function () {
        Route::get('/', [ProveedorController::class, 'index'])->name('index');
        Route::get('/crear', [ProveedorController::class, 'create'])->name('create');
        Route::post('/', [ProveedorController::class, 'store'])->name('store');
        Route::get('/{proveedor}/editar', [ProveedorController::class, 'edit'])->name('edit');
        Route::put('/{proveedor}', [ProveedorController::class, 'update'])->name('update');
        Route::patch('/{proveedor}/estado', [ProveedorController::class, 'cambiarEstado'])->name('estado');
    });
});

require __DIR__.'/auth.php';
