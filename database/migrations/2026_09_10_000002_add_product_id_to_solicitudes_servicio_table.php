<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Relaciona solicitudes de compra con el producto seleccionado del catálogo. */
return new class extends Migration
{
    /** La relación es opcional para mantener compatibilidad con tickets de soporte. */
    public function up(): void
    {
        Schema::table('solicitudes_servicio', function (Blueprint $table): void {
            $table->foreignId('product_id')->nullable()->after('user_id')->constrained('productos')->nullOnDelete();
        });
    }

    /** Quita la clave foránea y la columna del producto. */
    public function down(): void
    {
        Schema::table('solicitudes_servicio', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('product_id');
        });
    }
};