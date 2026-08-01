<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehiculo extends Model
{
    protected $table = 'vehiculo';
    protected $primaryKey = 'placa';
    
    // Placa es de tipo string y no es autoincremental
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'placa',
        'id_cliente',
        'marca',
        'modelo',
        'anio',
        'color',
    ];

    // Relación con el cliente propietario
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    public function propietario()
    {
        return $this->hasOneThrough(
            User::class,
            Cliente::class,
            'id_cliente',
            'cedula',
            'id_cliente',
            'cedula_users'
        );
    }

    // Relación con entradas
    public function entradas()
    {
        return $this->hasMany(Entrada::class, 'placa', 'placa');
    }
}
