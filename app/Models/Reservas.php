<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Huespedes;
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

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id');
    }

    public function asignaciones()
    {
        return $this->hasMany(Asignaciones_habitacion::class, 'reserva_id');
    }

    public function gastos()
    {
        return $this->hasMany(Gastos::class, 'reserva_id');
    }
}
