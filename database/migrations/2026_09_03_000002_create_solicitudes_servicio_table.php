<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Crea solicitudes de soporte y compra asociadas a la cuenta cliente. */
return new class extends Migration
{
    /** Define el caso, su seguimiento, prioridad y costos administrativos. */
    public function up(): void
    {
        Schema::create('solicitudes_servicio', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('tipo');
            $table->string('asunto');
            $table->text('descripcion');
            $table->string('estado')->default('pendiente');
            $table->string('prioridad')->default('normal');
            $table->decimal('valor', 12, 2)->nullable();
            $table->text('notas_admin')->nullable();
            $table->timestamps();
        });
    }

    /** Elimina la tabla de solicitudes al revertir la migración. */
    public function down(): void
    {
        Schema::dropIfExists('solicitudes_servicio');
    }
};