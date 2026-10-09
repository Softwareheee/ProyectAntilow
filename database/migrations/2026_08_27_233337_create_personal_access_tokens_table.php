<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Crea el almacenamiento de tokens personales utilizado por Sanctum. */
return new class extends Migration
{
    /** Registra tokens, permisos y fecha de último uso por usuario autenticable. */
    public function up(): void
    {
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->text('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /** Elimina la tabla de tokens al revertir la migración. */
    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};
