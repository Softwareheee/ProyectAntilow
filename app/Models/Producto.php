<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Periférico publicable con precio, existencia, imagen y atributos técnicos. */
class Producto extends Model
{
    protected $table = 'productos';

    protected $fillable = [
        'nombre', 'categoria', 'descripcion', 'precio', 'stock', 'imagen_url', 'caracteristicas', 'activo',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'caracteristicas' => 'array',
        'activo' => 'boolean',
    ];

    /** Solicitudes de compra asociadas a este producto. */
    public function solicitudes(): HasMany
    {
        return $this->hasMany(SolicitudServicio::class, 'product_id');
    }
}