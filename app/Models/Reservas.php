<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Huespedes;
use App\Models\Habitaciones;
use App\Models\User;
use App\Models\Asignaciones_habitacion;
use App\Models\Gastos;

class Reservas extends Model
{
    protected $table = 'reservas';
    protected $fillable = ['huesped_id', 'fecha_checkin', 'fecha_checkout', 'estado', 'usuario_id'];
    public $timestamps = false; // Usamos fecha_reserva

    protected $casts = [
        'fecha_reserva' => 'datetime',
        'fecha_checkin' => 'datetime',
        'fecha_checkout' => 'datetime',
    ];

    // Relaciones
    public function huesped()
    {
        return $this->belongsTo(Huespedes::class, 'huesped_id');
    }

    public function habitacion()
    {
        return $this->belongsTo(Habitaciones::class, 'habitacion_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id');
    }

    public function asignaciones()
    {
        return $this->hasMany(Asignaciones_habitacion::class, 'reserva_id');
    }
    // Asignación "vigente": sin fecha_fin (o la última por fecha_inicio)
    public function asignacionVigente()
    {
        return $this->hasOne(Asignaciones_habitacion::class, 'reserva_id')
            ->whereNull('fecha_fin')
            ->latest('fecha_inicio');
        // alternativa si siempre cerrás con fecha_fin:
        // ->whereDate('fecha_inicio', '<=', now())->whereDate('fecha_fin', '>=', now());
    }

    public function gastos()
    {
        return $this->hasMany(Gastos::class, 'reserva_id');
    }
}
