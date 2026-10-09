<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Añade visibilidad lógica para retirar productos sin borrarlos del inventario. */
return new class extends Migration
{
    /** Añade la columna solo cuando todavía no existe en el esquema. */
    public function up(): void
    {
        if (!Schema::hasColumn('productos', 'activo')) {
            Schema::table('productos', function (Blueprint $table): void {
                $table->boolean('activo')->default(true)->after('caracteristicas');
            });
        }
    }

    /** Quita la columna de visibilidad al revertir esta migración. */
    public function down(): void
    {
        if (Schema::hasColumn('productos', 'activo')) {
            Schema::table('productos', function (Blueprint $table): void {
                $table->dropColumn('activo');
            });
        }
    }
};
