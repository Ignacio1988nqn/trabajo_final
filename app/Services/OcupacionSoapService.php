<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class OcupacionSoapService
{

    function getConteoPorEstado()
    {
        $disponibles = DB::table('habitaciones')
            ->where('estado_actual', 'disponible')
            ->count();

        $ocupadas = DB::table('habitaciones')
            ->where('estado_actual', 'ocupada')
            ->count();

        $total = DB::table('habitaciones')->count();

        return [
            'disponible' => $disponibles,
            'ocupada' => $ocupadas,
            'total' => $total,
        ];
    }
}
