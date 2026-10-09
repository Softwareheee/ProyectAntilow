<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Registro de contacto administrado en el directorio de clientes. */
class Clientes extends Model
{
    protected $table = 'clientes';

    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'telefono',
        'direccion',
    ];
}
