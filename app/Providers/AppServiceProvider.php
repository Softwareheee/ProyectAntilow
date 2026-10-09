<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/** Punto de registro y arranque de servicios globales de Laravel. */
class AppServiceProvider extends ServiceProvider
{
    /** Aquí se registrarían enlaces del contenedor específicos de Antilow. */
    public function register(): void
    {
    }

    /** Punto para inicializaciones globales ejecutadas al arrancar Laravel. */
    public function boot(): void
    {
    }
}
