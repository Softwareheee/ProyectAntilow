<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComentarioReporte extends Model
{
    use HasFactory;

    protected $table = 'comentario_reportes';

    protected $fillable = [
        'reporte_servicio_id',
        'user_id',
        'tipo',
        'mensaje',
    ];

    /**
     * Relación con el reporte técnico al que pertenece el comentario.
     */
    public function reporte()
    {
        return $this->belongsTo(ReporteServicio::class, 'reporte_servicio_id');
    }

    /**
     * Relación con el usuario que creó el comentario/seguimiento.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}