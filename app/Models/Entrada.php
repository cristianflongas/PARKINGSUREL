<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entrada extends Model
{
    use HasFactory;

    protected $table = 'entrada';
    protected $primaryKey = 'id_entrada';

    protected $fillable = [
        'placa',
        'id_modulo',
        'id_personal',
        'id_tipo_servicio',
        'fecha_hora_entrada',
        'estado',
        'foto_entrada',
    ];

    protected $casts = [
        'fecha_hora_entrada' => 'datetime',
    ];

    // Relación con Vehículo
    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'placa', 'placa');
    }

    // Relación con Módulo
    public function modulo()
    {
        return $this->belongsTo(Modulo::class, 'id_modulo', 'id_modulo');
    }

    // Relación con Personal
    public function personal()
    {
        return $this->belongsTo(Personal::class, 'id_personal', 'id_personal');
    }

    // Relación con TipoServicio
    public function tipoServicio()
    {
        return $this->belongsTo(TipoServicio::class, 'id_tipo_servicio', 'id_tipo_servicio');
    }

    // Relación con Salida
    public function salida()
    {
        return $this->hasOne(Salida::class, 'id_entrada', 'id_entrada');
    }
}
