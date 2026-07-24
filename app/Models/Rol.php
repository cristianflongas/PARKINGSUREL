<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;

    protected $table = 'rol';
    protected $primaryKey = 'id_rol';
    
    protected $fillable = ['nombre_rol'];

    // Relación con Personal
    public function personal()
    {
        return $this->hasMany(Personal::class, 'id_rol');
    }
}
