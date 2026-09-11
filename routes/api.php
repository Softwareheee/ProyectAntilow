<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientesController;
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

    Route::middleware(EnsureUserIsAdmin::class)->group(function () {
        Route::apiResource('usuarios', App\Http\Controllers\UsuariosController::class)->except(['create', 'edit', 'show']);
        Route::patch('/solicitudes-servicio/{solicitudServicio}', [SolicitudesServicioController::class, 'update']);
        Route::delete('/solicitudes-servicio/{solicitudServicio}', [SolicitudesServicioController::class, 'destroy']);
        Route::apiResource('clientes', ClientesController::class);
    });
});
