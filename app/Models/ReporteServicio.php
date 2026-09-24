<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReporteServicio extends Model
{
    use HasFactory;

    protected $table = 'reportes_servicio';

    protected $fillable = [
        'solicitud_servicio_id',
        'admin_id',
        'diagnostico',
        'trabajo_realizado',
        'repuestos_utilizados',
        'costo_mano_obra',
        'costo_repuestos',
        'costo_total',
        'estado_atencion'
    ];

    public function solicitudservicio(): BelongsTo
    {
        return $this->belongsTo(SolicitudServicio::class, 'solicitud_servicio_id');
    }

    public function administrador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}