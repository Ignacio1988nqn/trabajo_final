<?php

namespace App\Http\Controllers;

use App\Models\Habitaciones;
use App\Models\Asignaciones_habitacion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class HabitacionController extends Controller
{
    public function create()
    {
        return Inertia::render('Habitaciones/Create');
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'numero'         => 'required|integer|min:1|unique:habitaciones,numero',
            'tipo'           => 'required|string|max:100',
            'precio_noche'   => 'required|numeric|min:0',
            'estado_actual'  => 'required|string|in:disponible,reservada,mantenimiento',
            'ultima_limpieza' => 'nullable|date',
        ]);
        Habitaciones::create($data);
        return redirect()->route('habitaciones.index')->with('success', 'Habitación creada');
    }

    public function index(Request $request)
    {
        $estado = $request->estado ?? 'todos';
        $tipo = $request->tipo ?? 'todos';
        $numero = $request->numero ?? null;

        $habitaciones = Habitaciones::query();

        if ($estado && $estado !== 'todos') {
            $habitaciones->where('estado_actual', $estado);
        }

        if ($tipo && $tipo !== 'todos') {
            $habitaciones->where('tipo', $tipo);
        }

        if ($numero) {
            $habitaciones->where('numero', 'like', $numero . '%');
        }

        $habitaciones = $habitaciones->get();

        // Obtener habitaciones disponibles
        $habitacionesDisponibles = Habitaciones::whereIn('estado_actual', ['disponible'])->get();
        // \Log::debug('Habitaciones disponibles enviadas:', $habitacionesDisponibles->toArray());

        // Obtener asignaciones vigentes para cada habitación
        $asignacionesVigentes = Asignaciones_habitacion::vigente()->get()->keyBy('habitacion_id');

        return Inertia::render('Habitaciones/Index', [
            'habitaciones' => $habitaciones->map(function ($habitacion) use ($asignacionesVigentes) {
                return [
                    'id' => $habitacion->id,
                    'numero' => $habitacion->numero,
                    'tipo' => $habitacion->tipo,
                    'precio_noche' => $habitacion->precio_noche,
                    'estado_actual' => $habitacion->estado_actual,
                    'ultima_limpieza' => $habitacion->ultima_limpieza,
                    'asignacion_vigente' => $asignacionesVigentes->get($habitacion->id),
                ];
            }),
            'habitacionesDisponibles' => $habitacionesDisponibles,
            'filtroEstado' => $estado,
            'filtroTipo' => $tipo,
            'filtroNumero' => $numero,
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

    public function cambiarHabitacion(Request $request, $habitacionId)
    {
        $request->validate([
            'nueva_habitacion_id' => 'required|exists:habitaciones,id',
            'motivo_cambio' => 'required|string|max:255',
            'asignacion_id' => 'required|exists:asignaciones_habitacion,id',
        ]);

        $habitacionActual = Habitaciones::findOrFail($habitacionId);
        $nuevaHabitacion = Habitaciones::findOrFail($request->nueva_habitacion_id);
        $asignacion = Asignaciones_habitacion::findOrFail($request->asignacion_id);

        // Validar que la asignación pertenece a la habitación actual
        if ($asignacion->habitacion_id != $habitacionId) {
            return redirect()->back()->withErrors(['error' => 'La asignación no corresponde a la habitación actual.']);
        }

        // Validar que la nueva habitación esté disponible o en limpieza
        if (!in_array($nuevaHabitacion->estado_actual, ['disponible', 'limpieza'])) {
            return redirect()->back()->withErrors(['error' => 'La nueva habitación no está disponible.']);
        }

        // Fecha actual
        $fechaActual = Carbon::now()->toDateString();

        // Actualizar la asignación existente con fecha_fin como la fecha actual
        $asignacion->update([
            'fecha_fin' => $fechaActual,
        ]);

        // Crear un nuevo registro para la nueva habitación
        $nuevaAsignacion = new Asignaciones_habitacion();
        $nuevaAsignacion->reserva_id = $asignacion->reserva_id;
        $nuevaAsignacion->habitacion_id = $nuevaHabitacion->id;
        $nuevaAsignacion->fecha_inicio = $fechaActual;
        $nuevaAsignacion->fecha_fin = $asignacion->fecha_fin; // Conserva la fecha_fin original
        $nuevaAsignacion->motivo_cambio = $request->motivo_cambio;
        $nuevaAsignacion->save();

        // Actualizar estados de las habitaciones
        $habitacionActual->estado_actual = 'limpieza';
        $nuevaHabitacion->estado_actual = 'ocupada';
        $habitacionActual->save();
        $nuevaHabitacion->save();

        return redirect()->back()->with('success', 'Habitación cambiada correctamente.');
    }
}
