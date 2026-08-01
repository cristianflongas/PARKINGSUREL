<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\ParqueaderoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\TipoServicioController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehiculoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Rutas protegidas que requieren autenticación
Route::middleware('auth')->group(function () {
    
    // Dashboard Principal - Dinámico
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Parqueadero — Vista unificada con módulos, entradas y salidas
    Route::get('/parqueadero', [ParqueaderoController::class, 'index'])->name('parqueadero');
    Route::post('/parqueadero/entrada', [ParqueaderoController::class, 'registrarEntrada'])->name('parqueadero.entrada');
    Route::post('/parqueadero/salida', [ParqueaderoController::class, 'registrarSalida'])->name('parqueadero.salida');
    Route::post('/parqueadero/ocr', [ParqueaderoController::class, 'procesarFotoOCR'])->name('parqueadero.ocr');

    // CRUD de Módulos (integrado en la vista Parqueadero)
    Route::post('/parqueadero/modulos', [ParqueaderoController::class, 'storeModulo'])->name('parqueadero.modulos.store');
    Route::put('/parqueadero/modulos/{id}', [ParqueaderoController::class, 'updateModulo'])->name('parqueadero.modulos.update');
    Route::delete('/parqueadero/modulos/{id}', [ParqueaderoController::class, 'destroyModulo'])->name('parqueadero.modulos.destroy');
    Route::post('/parqueadero/modulos/{id}/estado', [ParqueaderoController::class, 'cambiarEstadoModulo'])->name('parqueadero.modulos.estado');

    // Gestión de Vehículos y Propietarios
    Route::get('/vehiculos', [VehiculoController::class, 'index'])->name('vehiculos');
    Route::post('/vehiculos', [VehiculoController::class, 'store'])->name('vehiculos.store');
    Route::put('/vehiculos/{placa}', [VehiculoController::class, 'update'])->name('vehiculos.update');
    Route::delete('/vehiculos/{placa}', [VehiculoController::class, 'destroy'])->name('vehiculos.destroy');

    // Gestión de Pagos y Comprobantes
    Route::get('/pagos', [PagoController::class, 'index'])->name('pagos');
    Route::post('/pagos/procesar', [PagoController::class, 'procesarPago'])->name('pagos.procesar');
    Route::get('/pagos/{id}/comprobante', [PagoController::class, 'comprobante'])->name('pagos.comprobante');

    // Módulo de Reportes
    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes');
    Route::post('/reportes/guardar', [ReporteController::class, 'guardar'])->name('reportes.guardar');
    Route::post('/reportes/descargar-temporal', [ReporteController::class, 'descargarTemporal'])->name('reportes.descargar-temporal');
    Route::get('/reportes/{id}/pdf', [ReporteController::class, 'descargarPDF'])->name('reportes.pdf');
    Route::get('/reportes/{id}/excel', [ReporteController::class, 'descargarExcel'])->name('reportes.excel');
    Route::delete('/reportes/{id}', [ReporteController::class, 'destroy'])->name('reportes.destroy');

    // Rutas protegidas solo para ADMINISTRADOR
    Route::middleware('role:Administrador')->group(function () {
        Route::get('/usuarios', [UserController::class, 'index'])->name('usuarios');
        Route::post('/usuarios', [UserController::class, 'store'])->name('usuarios.store');
        Route::get('/usuarios/{id}/edit', [UserController::class, 'edit'])->name('usuarios.edit');
        Route::put('/usuarios/{id}', [UserController::class, 'update'])->name('usuarios.update');
        Route::delete('/usuarios/{id}', [UserController::class, 'destroy'])->name('usuarios.destroy');

        Route::get('/servicios', [TipoServicioController::class, 'index'])->name('servicios');
        Route::post('/servicios', [TipoServicioController::class, 'store'])->name('servicios.store');
        Route::get('/servicios/{id}/edit', [TipoServicioController::class, 'edit'])->name('servicios.edit');
        Route::put('/servicios/{id}', [TipoServicioController::class, 'update'])->name('servicios.update');
        Route::delete('/servicios/{id}', [TipoServicioController::class, 'destroy'])->name('servicios.destroy');
    });

    // Rutas de Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
