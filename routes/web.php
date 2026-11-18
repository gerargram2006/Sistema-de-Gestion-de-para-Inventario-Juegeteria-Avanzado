<?php

use App\Http\Controllers\AbastecimientoController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DevolucionController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\ProfileController;
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

        // Registro y modificación de juguetes restringidos a Administrador y Almacenero
        Route::middleware('role:administrador,almacenero')->group(function () {
            Route::post('/', [InventarioController::class, 'store'])->name('store');
            Route::put('/{producto}', [InventarioController::class, 'update'])->name('update');
            Route::patch('/{producto}/estado', [InventarioController::class, 'cambiarEstado'])->name('estado');
        });
    });

    // Módulo Punto de Venta (POS) y Tickets (CUS-01) - Vendedor y Administrador
    Route::middleware('role:vendedor,administrador')->prefix('ventas')->name('ventas.')->group(function () {
        Route::get('/', [VentaController::class, 'index'])->name('index');
        Route::get('/pos', [VentaController::class, 'pos'])->name('pos');
        Route::post('/', [VentaController::class, 'store'])->name('store');
        Route::post('/cliente', [VentaController::class, 'storeCliente'])->name('cliente.store');
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
    });

    // Módulo de Devoluciones y Cambios (CUS-06) - Vendedor, Cajero y Administrador
    Route::middleware('role:vendedor,cajero,administrador')->prefix('devoluciones')->name('devoluciones.')->group(function () {
        Route::get('/', [DevolucionController::class, 'index'])->name('index');
        Route::post('/{venta}', [DevolucionController::class, 'store'])->name('store');
    });

    // Gestión de Usuarios y Roles (reemplaza al registro público) - Solo Administrador
    Route::middleware('role:administrador')->prefix('usuarios')->name('usuarios.')->group(function () {
        Route::get('/', [UsuarioController::class, 'index'])->name('index');
        Route::post('/', [UsuarioController::class, 'store'])->name('store');
        Route::put('/{usuario}', [UsuarioController::class, 'update'])->name('update');
        Route::patch('/{usuario}/estado', [UsuarioController::class, 'cambiarEstado'])->name('estado');
    });

    // Perfil de Usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

require __DIR__.'/auth.php';
