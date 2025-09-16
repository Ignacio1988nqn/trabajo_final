<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Reservas;

class Gastos extends Model
{
    protected $table = 'gastos';
    protected $fillable = ['reserva_id', 'descripcion', 'monto', 'fecha', 'tipo'];
    public $timestamps = false; // Sin created_at/updated_at

    // Relaciones
    public function reserva()
    {
        return $this->belongsTo(Reservas::class, 'reserva_id');
    }
}
