<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/** Caso enviado por un cliente para soporte o para solicitar un producto. */
class SolicitudServicio extends Model
{
    protected $table = 'solicitudes_servicio';

    protected $fillable = [
        'user_id',
        'nombre_cliente',
        'telefono_cliente',
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

    /** Cuenta propietaria que creó el requerimiento. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Producto opcional relacionado con solicitudes de compra. */
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