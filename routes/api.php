<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientesController;
use App\Http\Controllers\ProductosController;
use App\Http\Controllers\ReportesController;
use App\Http\Controllers\SolicitudesServicioController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Rutas públicas sin token
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rutas privadas - con token
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/solicitudes-servicio', [SolicitudesServicioController::class, 'store']);
    Route::get('/solicitudes-servicio', [SolicitudesServicioController::class, 'index']);
    Route::get('/productos', [ProductosController::class, 'index']);
    
    // Rutas para consultar reportes
    Route::get('/reportes', [ReportesController::class, 'index']);
    Route::get('/reportes/{id}', [ReportesController::class, 'show']);
    
    // Ruta protegida para la descarga de factura en PDF
    Route::get('/reportes/{id}/factura/descargar', [ReportesController::class, 'descargarFactura']);

    Route::middleware(EnsureUserIsAdmin::class)->group(function () {
        Route::apiResource('usuarios', App\Http\Controllers\UsuariosController::class)->except(['create', 'edit', 'show']);
        Route::patch('/solicitudes-servicio/{solicitudServicio}', [SolicitudesServicioController::class, 'update']);
        Route::delete('/solicitudes-servicio/{solicitudServicio}', [SolicitudesServicioController::class, 'destroy']);
        Route::apiResource('clientes', ClientesController::class);
        Route::apiResource('productos', ProductosController::class)->except(['create', 'edit', 'show', 'index']);
        
        // Ruta exclusiva para que el admin cree un reporte
        Route::post('/reportes', [ReportesController::class, 'store']);
    });
});