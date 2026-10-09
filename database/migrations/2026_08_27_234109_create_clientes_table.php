<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Crea el directorio de datos de contacto administrado por Antilow. */
return new class extends Migration
{
    /** Crea clientes con correo único y contacto opcional. */
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('apellido');
            $table->string('email')->unique();
            $table->string('telefono')->nullable();
            $table->string('direccion')->nullable();
            $table->timestamps();
        });
    }

    /** Elimina el directorio al revertir esta migración. */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
