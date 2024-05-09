<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Turno extends Model
{
    use HasFactory;
    protected $fillable = ['id_juego','nroTurno','ganador','monto','fecha_inicio','fecha_fin']; 
}
