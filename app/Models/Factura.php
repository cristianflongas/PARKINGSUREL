<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    use HasFactory;

    protected $table = 'factura';
    protected $primaryKey = 'id_factura';

    protected $fillable = [
        'id_salida',
        'fecha_emision',
        'monto_total',
        'metodo_pago',
        'estado_pago',
    ];

    protected $casts = [
        'fecha_emision' => 'datetime',
        'monto_total' => 'decimal:2',
    ];

    // Relación con Salida
    public function salida()
    {
        return $this->belongsTo(Salida::class, 'id_salida', 'id_salida');
    }
}
