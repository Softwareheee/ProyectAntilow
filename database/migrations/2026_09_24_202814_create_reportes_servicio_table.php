<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Crea informes técnicos y sus importes asociados a una solicitud. */
return new class extends Migration
{
    /** Guarda diagnóstico, trabajo, costos y responsable administrativo. */
    public function up(): void
    {
        Schema::create('reportes_servicio', function (Blueprint $table) {
            $table->id();
            
            // Cada informe pertenece al caso de soporte atendido.
            $table->foreignId('solicitud_servicio_id')
                  ->constrained('solicitudes_servicio')
                  ->cascadeOnDelete();
            
            // Conserva quién realizó y firmó la intervención.
            $table->foreignId('admin_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
                  
            $table->text('diagnostico');
            $table->text('trabajo_realizado');
            $table->text('repuestos_utilizados')->nullable();
            
            $table->decimal('costo_mano_obra', 12, 2)->default(0.00);
            $table->decimal('costo_repuestos', 12, 2)->default(0.00);
            $table->decimal('costo_total', 12, 2)->default(0.00);

            $table->string('estado_atencion')->default('completado');

            $table->timestamps();
        });
    }

    /** Elimina los informes al revertir la migración. */
    public function down(): void
    {
        Schema::dropIfExists('reportes_servicio');
    }
};