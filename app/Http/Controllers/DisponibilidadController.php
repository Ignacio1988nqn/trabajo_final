<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Habitaciones;
use Inertia\Inertia;


class DisponibilidadController extends Controller
{
    public function index(Request $request)
    {
        $tipo = $request->input('tipo');
        $fecha_inicio = $request->input('fecha_inicio');
        $fecha_fin = $request->input('fecha_fin');

        $habitaciones = collect();

        if ($tipo && $fecha_inicio && $fecha_fin) {
            $habitaciones = Habitaciones::where('tipo', $tipo)
                ->where('estado_actual', '!=', 'mantenimiento') // Opcional: excluir mantenimiento, ajusta según necesidades
                ->whereDoesntHave('asignaciones', function ($q) use ($fecha_inicio, $fecha_fin) {
                    $q->where('fecha_inicio', '<', $fecha_fin)
                        ->where(function ($q2) use ($fecha_inicio) {
                            $q2->where('fecha_fin', '>', $fecha_inicio)
                                ->orWhereNull('fecha_fin');
                        });
                })
                ->get();
        }

        return Inertia::render('Reservas/Disponibilidad', [
            'habitaciones' => $habitaciones,
            'tipo' => $tipo,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
        ]);
    }
}
