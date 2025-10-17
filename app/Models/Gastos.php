<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Reservas;
use App\Models\GastoItems;

class Gastos extends Model
{
    protected $table = 'gastos';
    protected $fillable = [
        'reserva_id',
        'gasto_item_id',
        'fecha',
        'monto',

    ];
    public $timestamps = false;

    public function reserva()
    {
        return $this->belongsTo(Reservas::class, 'reserva_id');
    }

    public function item()
    {
        return $this->belongsTo(GastoItems::class, 'gasto_item_id');
    }
}
