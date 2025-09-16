<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Reservas;
use App\Models\Habitaciones;

class Asignaciones_habitacion extends Model
{
    protected $table = 'asignaciones_habitacion';
    protected $fillable = ['reserva_id', 'habitacion_id', 'fecha_inicio', 'fecha_fin', 'motivo_cambio'];
    public $timestamps = false; // Sin created_at/updated_at

    // Relaciones
    public function reserva()
    {
        return $this->belongsTo(Reservas::class, 'reserva_id');
    }

    public function habitacion()
    {
        return $this->belongsTo(Habitaciones::class, 'habitacion_id');
    }
}
