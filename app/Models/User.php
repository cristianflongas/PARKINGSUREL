<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, CanResetPassword;

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

    // users guarda la identidad base de ambas categorías:
    // - personal del parqueadero (creado desde módulo Usuarios)
    // - clientes/propietarios (creado desde módulo Vehículos)
    // Cada perfil tiene su registro específico en personal o cliente.
    public function personal()
    {
        return $this->hasOne(Personal::class, 'cedula_users', 'cedula');
    }

    public function cliente()
    {
        return $this->hasOne(Cliente::class, 'cedula_users', 'cedula');
    }

    public function vehiculos()
    {
        return $this->hasManyThrough(
            Vehiculo::class,
            Cliente::class,
            'cedula_users',
            'id_cliente',
            'cedula',
            'id_cliente'
        );
    }

    public function getEmailForPasswordReset(): string
    {
        return $this->correo ?? '';
    }

    public function getEmailAttribute(): ?string
    {
        return $this->correo;
    }

    public function setEmailAttribute($value): void
    {
        $this->attributes['correo'] = $value;
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPassword($token));
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
