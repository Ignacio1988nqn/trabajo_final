<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Asignaciones_habitacion;
use App\Models\Estados_habitacion;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Reservas;

class Habitaciones extends Model
{
    protected $table = 'habitaciones';
    protected $fillable = ['numero', 'tipo', 'precio_noche', 'estado_actual', 'ultima_limpieza'];
    public $timestamps = false; // Sin created_at/updated_at


    public function reservas(): HasMany
    {
        return $this->hasMany(Reservas::class);
    }

    // Filtro útil para disponibilidad
    public function scopeDisponibleEntre($q, $in, $out)
    {
        if (!$in || !$out) return $q;
        return $q->whereDoesntHave('reservas', function ($qq) use ($in, $out) {
            $qq->where('fecha_checkin', '<', $out)
                ->where('fecha_checkout', '>', $in);
        });
    }

    // 
    public function asignaciones()
    {
        return $this->hasMany(Asignaciones_habitacion::class, 'habitacion_id');
    }

    public function estados()
    {
        return $this->hasMany(Estados_habitacion::class, 'habitacion_id');
    }
}
