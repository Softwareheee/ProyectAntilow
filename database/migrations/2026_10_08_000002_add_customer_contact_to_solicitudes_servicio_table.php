<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes_servicio', function (Blueprint $table): void {
            $table->string('nombre_cliente')->nullable()->after('user_id');
            $table->string('telefono_cliente', 30)->nullable()->after('nombre_cliente');
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_servicio', function (Blueprint $table): void {
            $table->dropColumn(['nombre_cliente', 'telefono_cliente']);
        });
    }
};
