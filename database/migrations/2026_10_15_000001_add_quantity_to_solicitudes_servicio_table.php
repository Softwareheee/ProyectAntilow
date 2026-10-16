<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes_servicio', function (Blueprint $table): void {
            $table->unsignedSmallInteger('cantidad')->default(1)->after('product_id');
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_servicio', function (Blueprint $table): void {
            $table->dropColumn('cantidad');
        });
    }
};
