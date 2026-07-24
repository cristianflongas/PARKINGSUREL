<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $primaryKey = 'cedula';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cedula',
        'nombre',
        'telefono',
        'correo',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // Relación con Personal
    public function personal()
    {
        return $this->hasOne(Personal::class, 'cedula_users', 'cedula');
    }

    // Relación con Rol a través de Personal
    public function rol()
    {
        return $this->hasOneThrough(
            Rol::class,
            Personal::class,
            'cedula_users',
            'id_rol',
            'cedula',
            'id_rol'
        );
    }
}
