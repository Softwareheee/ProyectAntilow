<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('comentario_reportes', function (Blueprint $table) {
    $table->id();
    $table->foreignId('reporte_servicio_id')->constrained('reportes_servicio')->onDelete('cascade');
    $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
    $table->enum('tipo', ['felicitacion', 'falla_post_mantenimiento', 'duda', 'sugerencia']);
    $table->text('mensaje');
    $table->timestamps();
});
}
};
