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

    protected $casts = [
        'password_hash' => 'hashed',
    ];

    /**
     * Get the password for the user.
     * Laravel usa este método para saber qué campo contiene la contraseña hasheada
     */
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    // Relación con el usuario (por si necesitas traer su nombre o correo)
    public function user()
    {
        return $this->belongsTo(User::class, 'cedula_users', 'cedula');
    }

    // Relación con Rol
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }
}