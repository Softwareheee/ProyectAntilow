<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SolicitudServicio extends Model
{
    protected $table = 'solicitudes_servicio';

    protected $fillable = [
        'user_id',
        'product_id',
        'tipo',
        'asunto',
        'descripcion',
        'estado',
        'prioridad',
        'valor',
        'notas_admin',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'product_id');
    }

    /**
     * Relación con el reporte técnico creado por el administrador.
     */
    public function reporte(): HasOne
    {
        return $this->hasOne(ReporteServicio::class, 'solicitud_servicio_id');
    }
}