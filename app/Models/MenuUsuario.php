<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuUsuario extends Model
{
    use HasFactory;

    protected $table = 'menu_usuario';
    protected $fillable = ['nombre', 'ruta', 'icono', 'rol', 'padre_id', 'orden', 'activo'];

    public function hijos()
    {
        return $this->hasMany(MenuUsuario::class, 'padre_id')->orderBy('orden');
    }

    public function padre()
    {
        return $this->belongsTo(MenuUsuario::class, 'padre_id');
    }
}
