<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Reservas;
use App\Models\Habitaciones;
use App\Models\ReservaDetalle;

class Asignaciones_habitacion extends Model
{
    protected $table = 'asignaciones_habitacion';
    public $timestamps = false;
    protected $fillable = ['reserva_id', 'habitacion_id', 'fecha_inicio', 'fecha_fin', 'motivo_cambio','reserva_detalle_id','reserva_detalle_id'];

    public function reserva()
    {
        return $this->belongsTo(Reservas::class, 'reserva_id');
    }
    public function habitacion()
    {
        return $this->belongsTo(Habitaciones::class, 'habitacion_id');
    }

    public function reservaDetalle()
    {
        return $this->belongsTo(ReservaDetalle::class);
    }

    // scope para obtener la asignación vigente en una fecha
    public function scopeVigente($q, $fecha = null)
    {
        $f = $fecha ?: now()->toDateString();
        return $q->where('fecha_inicio', '<=', $f)
            ->where(function ($qq) use ($f) {
                $qq->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', $f);
            });
    }
}
