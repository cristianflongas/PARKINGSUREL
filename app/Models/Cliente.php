<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'cliente';
    protected $primaryKey = 'id_cliente';

    protected $fillable = [
        'cedula_users',
    ];

    // Relación con el usuario
    public function user()
    {
        return $this->belongsTo(User::class, 'cedula_users', 'cedula');
    }

    // Relación con vehículos
    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class, 'id_cliente', 'id_cliente');
    }
}
