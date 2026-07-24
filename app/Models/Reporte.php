<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reporte extends Model
{
    use HasFactory;

    protected $table = 'reportes';
    protected $primaryKey = 'id_reporte';

    protected $fillable = [
        'nombre_reporte',
        'tipo_reporte',
        'fecha_inicio',
        'fecha_fin',
        'total_recaudado',
        'contenido',
        'observaciones',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'total_recaudado' => 'decimal:2',
        'contenido' => 'array',
    ];
}
