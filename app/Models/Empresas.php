<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Huespedes;

class Empresas extends Model
{
    protected $table = 'empresas';
    protected $primaryKey = 'huesped_id';
    public $incrementing = false; // huesped_id no es auto-incremental
    public $timestamps = false; // Sin created_at/updated_at
    protected $fillable = ['huesped_id', 'razon_social', 'cuit'];

    // Relaciones
    public function huesped()
    {
        return $this->belongsTo(Huespedes::class, 'huesped_id');
    }
}
