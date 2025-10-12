<?php

namespace App\Http\Controllers;

use App\Models\Habitaciones;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HabitacionController extends Controller
{
    public function index(Request $request)
    {

        $estado = $request->estado ?? 'todos';
        $tipo = $request->tipo ?? 'todos';

        $habitaciones = Habitaciones::query();

        if ($estado && $estado !== 'todos') {
            $habitaciones->where('estado_actual', $estado);
        }

        if ($tipo && $tipo !== 'todos') {
            $habitaciones->where('tipo', $tipo);
        }

        $habitaciones = $habitaciones->get();

        return Inertia::render('Habitaciones/Index', [
            'habitaciones' => $habitaciones,
            'filtroEstado' => $estado,
            'filtroTipo' => $tipo,
        ]);
    }

    public function actualizarEstado(Request $request, $id)
{
    $habitacion = Habitaciones::findOrFail($id);

    $request->validate([
        'estado_actual' => 'required|string|in:disponible,ocupada,mantenimiento,limpieza',
    ]);

    $habitacion->estado_actual = $request->estado_actual;
    $habitacion->save();

    return redirect()->back()->with('success', 'Estado actualizado correctamente.');
}
}
