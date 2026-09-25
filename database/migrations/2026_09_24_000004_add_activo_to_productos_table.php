<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('productos', 'activo')) {
            Schema::table('productos', function (Blueprint $table): void {
                $table->boolean('activo')->default(true)->after('caracteristicas');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('productos', 'activo')) {
            Schema::table('productos', function (Blueprint $table): void {
                $table->dropColumn('activo');
            });
        }
    }
};
