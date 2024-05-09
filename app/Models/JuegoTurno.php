<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JuegoTurno extends Model
{
    use HasFactory;
    protected $fillable = ['id_turno','jugador','pago']; 
}
