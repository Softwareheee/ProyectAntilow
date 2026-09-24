<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_servicio');
    }
};