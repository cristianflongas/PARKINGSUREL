<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salida extends Model
{
    use HasFactory;

    protected $table = 'salida';
    protected $primaryKey = 'id_salida';

    protected $fillable = [
        'id_entrada',
        'fecha_hora_salida',
        'foto_salida',
    ];

    protected $casts = [
        'fecha_hora_salida' => 'datetime',
    ];

    // Relación con Entrada
    public function entrada()
    {
        return $this->belongsTo(Entrada::class, 'id_entrada', 'id_entrada');
    }

    // Relación con Factura
    public function factura()
    {
        return $this->hasOne(Factura::class, 'id_salida', 'id_salida');
    }
}
