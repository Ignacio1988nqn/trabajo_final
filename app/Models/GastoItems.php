<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Gastos;

class GastoItems extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'descripcion', 'precio', 'tipo','stock'];

    public function gastos()
    {
        return $this->hasMany(Gastos::class, 'gasto_item_id');
    }
}
