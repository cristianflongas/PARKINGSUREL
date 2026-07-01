<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Personal extends Authenticatable
{
    use Notifiable;

    protected $table = 'personal';
    protected $primaryKey = 'id_personal';

    protected $fillable = [
        'cedula_users',
        'id_rol',
        'usuario',
        'password_hash',
    ];

    protected $hidden = [
        'password_hash',
    ];

    // Relación con el usuario (por si necesitas traer su nombre o correo)
    public function user()
    {
        return $this->belongsTo(User::class, 'cedula_users', 'cedula');
    }

    // Le indicamos a Laravel que tu columna de contraseña se llama 'password_hash'
    public function getAuthPassword()
    {
        return $this->password_hash;
    }
}