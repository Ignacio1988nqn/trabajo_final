<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Habitaciones;
use App\Models\User;


class Estados_habitacion extends Model
{
    protected $table = 'estados_habitacion';
    protected $fillable = ['habitacion_id', 'tipo_estado', 'valor', 'usuario_id'];
    public $timestamps = false; // Usamos fecha

    // Relaciones
    public function habitacion()
    {
        return $this->belongsTo(Habitaciones::class, 'habitacion_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
