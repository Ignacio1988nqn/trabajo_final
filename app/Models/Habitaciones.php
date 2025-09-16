<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Asignaciones_habitacion;
use App\Models\Estados_habitacion;

class Habitaciones extends Model
{
    protected $table = 'habitaciones';
    protected $fillable = ['numero', 'tipo', 'precio_noche', 'estado_actual', 'ultima_limpieza'];
    public $timestamps = false; // Sin created_at/updated_at

    // Relaciones
    public function asignaciones()
    {
        return $this->hasMany(Asignaciones_habitacion::class, 'habitacion_id');
    }

    public function estados()
    {
        return $this->hasMany(Estados_habitacion::class, 'habitacion_id');
    }
}
