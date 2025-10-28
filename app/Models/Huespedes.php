<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Personas;
use App\Models\Empresas;
use App\Models\Reservas;


class Huespedes extends Model
{
    protected $table = 'huespedes';
    protected $fillable = ['tipo_huesped', 'telefono', 'email', 'fecha_registro'];
    public $timestamps = false; // Usamos fecha_registro

    // Relaciones
    public function personas()
    {
        return $this->hasOne(Personas::class, 'huesped_id');
    }

    public function empresas()
    {
        return $this->hasOne(Empresas::class, 'huesped_id');
    }

    public function reservas()
    {
        return $this->hasMany(Reservas::class, 'huesped_id');
    }


    // Etiqueta lista para usar en el <select>
    public function getDisplayAttribute(): string
    {
        if ($this->tipo_huesped === 'persona' && $this->persona) {
            $p = $this->persona;
            return trim("{$p->apellido}, {$p->nombre} (DNI: {$p->documento})");
        }
        if ($this->tipo_huesped === 'empresa' && $this->empresa) {
            $e = $this->empresa;
            return "{$e->razon_social} (CUIT: {$e->cuit})";
        }
        return "Huésped #{$this->id}";
    }
}
