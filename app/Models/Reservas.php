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

    // si tenés 'observaciones' y 'fecha_reserva' en la tabla, agregalos acá
    protected $fillable = [
        'huesped_id',
        'fecha_checkin',
        'fecha_checkout',
        'estado',
        'usuario_id',
        'fecha_reserva',
        'observaciones',
    ];

    public $timestamps = false; // usamos fecha_reserva manualmente

    protected $casts = [
        'fecha_reserva' => 'date',
        'fecha_checkin' => 'date',
        'fecha_checkout' => 'date',
    ];

    /* ------------ Relaciones ------------ */

    public function huesped()
    {
        return $this->belongsTo(Huespedes::class, 'huesped_id');
    }

    // ⚠️ Quitar esta relación si reservas NO tiene columna habitacion_id
    // public function habitacion()
    // {
    //     return $this->belongsTo(Habitaciones::class, 'habitacion_id');
    // }

    public function usuario()
    {
        // FK correcta:
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function asignaciones()
    {
        return $this->hasMany(Asignaciones_habitacion::class, 'reserva_id');
    }

    public function gastos()
    {
        return $this->hasMany(Gastos::class, 'reserva_id');
    }

    /* ------------ Helpers de vigencia ------------ */

    /**
     * Asignaciones vigentes para una fecha de referencia ($fechaRef: 'Y-m-d').
     * Vigente := fecha_inicio <= ref && (fecha_fin IS NULL || fecha_fin >= ref)
     */
    public function asignacionesVigentes(string $fechaRef)
    {
        return $this->asignaciones()
            ->where('fecha_inicio', '<=', $fechaRef)
            ->where(function ($q) use ($fechaRef) {
                $q->whereNull('fecha_fin')
                    ->orWhere('fecha_fin', '>=', $fechaRef);
            });
    }

    /* DEVUELVE TODAS LAS ASIGNACIONES VIGENTES*/
    public function asignacionesVigentesA(string $fecha)
    {
        return $this->asignaciones()
            ->whereDate('fecha_inicio', '<=', $fecha)
            ->where(function ($q) use ($fecha) {
                $q->whereNull('fecha_fin')
                    ->orWhereDate('fecha_fin', '>=', $fecha);
            });
    }


    /**
     * Última asignación vigente a HOY.
     * Requiere Laravel 9+ para latestOfMany; si no, usar orderByDesc->first() en el controlador.
     */
    public function asignacionVigenteHoy()
    {
        $hoy = now()->toDateString();

        return $this->hasOne(Asignaciones_habitacion::class, 'reserva_id')
            ->where('fecha_inicio', '<=', $hoy)
            ->where(function ($q) use ($hoy) {
                $q->whereNull('fecha_fin')
                    ->orWhere('fecha_fin', '>=', $hoy);
            })
            ->latestOfMany('fecha_inicio');
    }

    /**
     * Última asignación vigente a la fecha de check-in planificada.
     */
    public function asignacionVigentePlanificada()
    {
        return $this->hasOne(Asignaciones_habitacion::class, 'reserva_id')
            ->whereColumn('fecha_inicio', '<=', 'reservas.fecha_checkin')
            ->where(function ($q) {
                $q->whereNull('fecha_fin')
                    ->orWhereColumn('fecha_fin', '>=', 'reservas.fecha_checkin');
            })
            ->latestOfMany('fecha_inicio');
    }

    /**
     * Mantengo un alias compatible con lo que ya usabas.
     * Por defecto, vigencia HOY (podés cambiar al planificado si te conviene).
     */
    public function asignacionVigente()
    {
        return $this->asignacionVigenteHoy();
    }
}
