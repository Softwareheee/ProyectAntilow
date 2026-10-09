<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Cuenta autenticable con token Sanctum, rol y solicitudes asociadas. */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /** Campos permitidos para alta y edición masiva de cuentas. */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /** Credenciales y tokens nunca deben salir en respuestas JSON. */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /** Convierte fechas verificadas y aplica hashing al asignar contraseña. */
    protected $casts = [
     'email_verified_at' => 'datetime',
     'password' => 'hashed',
    ];

    /** Solicitudes de soporte o compra creadas por esta cuenta. */
    public function solicitudes(): HasMany
    {
        return $this->hasMany(SolicitudServicio::class);
    }
}
