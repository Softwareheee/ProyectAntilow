<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientesController;
use App\Http\Controllers\ProductosController;
use App\Http\Controllers\ReportesController;
use App\Http\Controllers\SolicitudesServicioController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Endpoints de lectura pública y autenticación; no requieren token Sanctum.
Route::get('/openapi.json', function () {
    $specification = json_decode(
        file_get_contents(public_path('openapi.json')),
        true,
        512,
        JSON_THROW_ON_ERROR,
    );

    return response()->json($specification);
});
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/productos', [ProductosController::class, 'index']);

// El grupo requiere un token Sanctum válido para operaciones de cliente.
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/solicitudes-servicio', [SolicitudesServicioController::class, 'store']);
    Route::get('/solicitudes-servicio', [SolicitudesServicioController::class, 'index']);

    // Reportes visibles para usuarios autenticados; su creación sí es administrativa.
    Route::get('/reportes', [ReportesController::class, 'index']);
    Route::get('/reportes/{id}', [ReportesController::class, 'show']);
    Route::get('/reportes/{id}/factura/descargar', [ReportesController::class, 'descargarFactura']);

    // Operaciones de gestión de datos reservadas a cuentas con role=admin.
    Route::middleware(EnsureUserIsAdmin::class)->group(function () {
        Route::apiResource('usuarios', App\Http\Controllers\UsuariosController::class)->except(['create', 'edit', 'show']);
        Route::patch('/solicitudes-servicio/{solicitudServicio}', [SolicitudesServicioController::class, 'update']);
        Route::delete('/solicitudes-servicio/{solicitudServicio}', [SolicitudesServicioController::class, 'destroy']);
        Route::get('/solicitudes-servicio/{solicitudServicio}/factura-compra/descargar', [ReportesController::class, 'descargarFacturaCompra']);
        Route::apiResource('clientes', ClientesController::class);
        Route::apiResource('productos', ProductosController::class)->except(['create', 'edit', 'show', 'index']);
        
        // Crear el informe cierra la solicitud y envía la factura al cliente.
        Route::post('/reportes', [ReportesController::class, 'store']);
    });
});