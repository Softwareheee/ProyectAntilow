<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Añade el rol que separa permisos de administrador y cliente. */
return new class extends Migration
{
    /** Las cuentas existentes quedan como clientes por defecto. */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('role')->default('client')->after('password');
        });
    }

    /** Revierte la columna agregada por esta migración. */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('role');
        });
    }
};