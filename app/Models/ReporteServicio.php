<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReporteServicio extends Model
{
    use HasFactory;

    protected $table = 'reportes_servicio';

    protected $fillable = [
        'solicitud_servicio_id',
        'admin_id',
        'nombre_cliente',
        'telefono_cliente',
        'diagnostico',
        'trabajo_realizado',
        'repuestos_utilizados',
        'costo_mano_obra',
        'costo_repuestos',
        'costo_total',
        'estado_atencion',
    ];

    public function solicitudservicio()
    {
        return $this->belongsTo(SolicitudServicio::class, 'solicitud_servicio_id');
    }

    public function administrador()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    // --- AGREGA ESTA RELACIÓN QUE FALTA ---
    public function comentarios()
    {
        return $this->hasMany(ComentarioReporte::class, 'reporte_servicio_id');
    }
}