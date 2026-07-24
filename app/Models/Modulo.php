<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    protected $table = 'modulo';
    protected $primaryKey = 'id_modulo';

    protected $fillable = [
        'ubicacion',
        'estado',
    ];

    // Relación con entradas
    public function entradas()
    {
        return $this->hasMany(Entrada::class, 'id_modulo', 'id_modulo');
    }
}
