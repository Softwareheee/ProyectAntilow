<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function solicitudes(): HasMany
    {
        return $this->hasMany(SolicitudServicio::class, 'product_id');
    }
}