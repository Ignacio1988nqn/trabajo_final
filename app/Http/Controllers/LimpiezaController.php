<?php

namespace App\Http\Controllers;

use App\Models\Habitaciones;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LimpiezaController extends Controller
{
    public function index()
    {
        $habitaciones = Habitaciones::where('estado_actual', 'limpieza')->get();

        return Inertia::render('Limpieza/Index', [
            'habitaciones' => $habitaciones,
        ]);
    }

    public function marcarDisponible($id)
    {
        $habitacion = Habitaciones::findOrFail($id);
        $habitacion->estado_actual = 'disponible';
        $habitacion->ultima_limpieza = now();
        $habitacion->save();

        return redirect()->route('limpieza.index')
            ->with('success', 'Habitación marcada como disponible.');
    }
}
