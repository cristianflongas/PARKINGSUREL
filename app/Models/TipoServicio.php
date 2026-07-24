<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoServicio extends Model
{
    use HasFactory;

    protected $table = 'tipo_servicio';
    protected $primaryKey = 'id_tipo_servicio';

    protected $fillable = [
        'nombre_tipo_servicio',
        'descripcion',
        'tarifa',
        'estado',
    ];

    protected $casts = [
        'tarifa' => 'decimal:2',
    ];
}
