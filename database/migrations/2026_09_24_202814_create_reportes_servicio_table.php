<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reportes_servicio', function (Blueprint $table) {
            $table->id();
            
            // Relación con solicitudes_servicio
            $table->foreignId('solicitud_servicio_id')
                  ->constrained('solicitudes_servicio')
                  ->cascadeOnDelete();
            
            // Relación con el usuario administrador que redacta el reporte
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

    public function down(): void
    {
        Schema::dropIfExists('reportes_servicio');
    }
};