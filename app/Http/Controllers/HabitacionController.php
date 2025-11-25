<?php

namespace App\Http\Controllers;

use Illuminate\Validation\ValidationException;

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

        $habitaciones->orderByRaw('CAST(numero AS UNSIGNED)');
        $habitaciones = $habitaciones->get();
        $habitacionesDisponibles = Habitaciones::whereIn('estado_actual', ['disponible'])->get();

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
    // private function hayConflictoEnHabitacion(
    //     int $habitacionId,
    //     string $ini,
    //     string $fin,
    //     ?int $ignorarAsignacionId = null
    // ): bool {
    //     return Asignaciones_habitacion::where('habitacion_id', $habitacionId)
    //         ->when($ignorarAsignacionId, fn($q) => $q->where('id', '!=', $ignorarAsignacionId))
    //         ->where('fecha_inicio', '<', $fin)
    //         ->where(function ($q) use ($ini) {
    //             $q->where('fecha_fin', '>', $ini)
    //                 ->orWhereNull('fecha_fin');
    //         })
    //         ->exists();
    // }
    private function obtenerConflictoEnHabitacion(
        int $habitacionId,
        string $ini,
        string $fin,
        ?int $ignorarAsignacionId = null
    ): ?Asignaciones_habitacion {
        return Asignaciones_habitacion::where('habitacion_id', $habitacionId)
            ->when($ignorarAsignacionId, fn($q) => $q->where('id', '!=', $ignorarAsignacionId))
            ->where('fecha_inicio', '<', $fin)
            ->where(function ($q) use ($ini) {
                $q->where('fecha_fin', '>', $ini)
                    ->orWhereNull('fecha_fin');
            })
            ->first(); // 👈 nos quedamos con el primer conflicto
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
            'motivo_cambio'       => 'required|string|max:255',
            'asignacion_id'       => 'required|exists:asignaciones_habitacion,id',
        ]);

        $habitacionActual = Habitaciones::findOrFail($habitacionId);
        $nuevaHabitacion  = Habitaciones::findOrFail($request->nueva_habitacion_id);
        $asignacion       = Asignaciones_habitacion::findOrFail($request->asignacion_id);

        if ($asignacion->habitacion_id != $habitacionId) {
            return back()->withErrors([
                'error' => 'La asignación no corresponde a la habitación actual.'
            ]);
        }

        $fechaInicioNueva = Carbon::today()->toDateString();
        $fechaFinOriginal = $asignacion->fecha_fin; // puede ser null
        $finParaCheck     = $fechaFinOriginal ?? Carbon::today()->copy()->addYears(5)->toDateString();

        // 🔎 Buscamos conflicto con la nueva habitación
        $conflicto = $this->obtenerConflictoEnHabitacion(
            $nuevaHabitacion->id,
            $fechaInicioNueva,
            $finParaCheck,
            null
        );

        if ($conflicto) {
            $finTexto = $conflicto->fecha_fin
                ? $conflicto->fecha_fin
                : 'sin fecha de salida definida';

            // 👇 ESTO es lo importante: lanzamos una ValidationException (422)
            throw ValidationException::withMessages([
                'error' => "La nueva habitación ya tiene una reserva entre "
                    . "{$conflicto->fecha_inicio} y {$finTexto}.",
            ]);
        }
        // ================================
        // 2) CREAR LA NUEVA ASIGNACIÓN
        // ================================
        $nuevaAsignacion = new Asignaciones_habitacion();
        $nuevaAsignacion->reserva_id         = $asignacion->reserva_id;
        $nuevaAsignacion->habitacion_id      = $nuevaHabitacion->id;
        $nuevaAsignacion->fecha_inicio       = $fechaInicioNueva;
        $nuevaAsignacion->fecha_fin          = $fechaFinOriginal; // 👈 mantenemos el fin original
        $nuevaAsignacion->reserva_detalle_id = $asignacion->reserva_detalle_id;
        $nuevaAsignacion->motivo_cambio      = $request->motivo_cambio;
        $nuevaAsignacion->save();

        // 3) Cerramos la anterior
        $asignacion->update([
            'fecha_fin' => $fechaInicioNueva,
        ]);

        // 4) Estados visuales
        $habitacionActual->estado_actual = 'limpieza';
        $habitacionActual->save();

        $nuevaHabitacion->estado_actual = 'ocupada';
        $nuevaHabitacion->save();

        return back()->with('success', 'Habitación cambiada correctamente.');
    }
    // public function cambiarHabitacion(Request $request, $habitacionId)
    // {
    //     $request->validate([
    //         'nueva_habitacion_id' => 'required|exists:habitaciones,id',
    //         'motivo_cambio'       => 'required|string|max:255',
    //         'asignacion_id'       => 'required|exists:asignaciones_habitacion,id',
    //     ]);

    //     $habitacionActual = Habitaciones::findOrFail($habitacionId);
    //     $nuevaHabitacion  = Habitaciones::findOrFail($request->nueva_habitacion_id);
    //     $asignacion       = Asignaciones_habitacion::findOrFail($request->asignacion_id);

    //     if ($asignacion->habitacion_id != $habitacionId) {
    //         return back()->withErrors([
    //             'error' => 'La asignación no corresponde a la habitación actual.'
    //         ]);
    //     }

    //     // ================================
    //     // 1) RANGOS DE FECHA
    //     // ================================
    //     $fechaInicioNueva = Carbon::today()->toDateString();

    //     // 👉 IMPORTANTE: conservar la fecha_fin original (puede ser null)
    //     $fechaFinOriginal = $asignacion->fecha_fin;

    //     // Para chequear conflictos, si es null usamos una fecha “lejana”
    //     $finParaCheck = $fechaFinOriginal ?? Carbon::today()->copy()->addYears(5)->toDateString();

    //     if ($this->hayConflictoEnHabitacion(
    //         $nuevaHabitacion->id,
    //         $fechaInicioNueva,
    //         $finParaCheck
    //     )) {
    //         return back()->withErrors([
    //             'error' => 'La nueva habitación no está disponible en ese rango.'
    //         ]);
    //     }

    //     // ================================
    //     // 2) CREAR LA NUEVA ASIGNACIÓN
    //     // ================================
    //     $nuevaAsignacion = new Asignaciones_habitacion();
    //     $nuevaAsignacion->reserva_id         = $asignacion->reserva_id;
    //     $nuevaAsignacion->habitacion_id      = $nuevaHabitacion->id;
    //     $nuevaAsignacion->fecha_inicio       = $fechaInicioNueva;
    //     $nuevaAsignacion->fecha_fin          = $fechaFinOriginal; // 👈 se mantiene el fin original (o null)
    //     $nuevaAsignacion->reserva_detalle_id = $asignacion->reserva_detalle_id;
    //     $nuevaAsignacion->motivo_cambio      = $request->motivo_cambio;
    //     $nuevaAsignacion->save();

    //     // ================================
    //     // 3) CERRAR LA ASIGNACIÓN ANTERIOR
    //     // ================================
    //     $asignacion->update([
    //         'fecha_fin' => $fechaInicioNueva, // la vieja termina HOY
    //     ]);

    //     // ================================
    //     // 4) ESTADOS VISUALES DE HABITACIONES
    //     // ================================
    //     $habitacionActual->estado_actual = 'limpieza';
    //     $habitacionActual->save();

    //     $nuevaHabitacion->estado_actual = 'ocupada';
    //     $nuevaHabitacion->save();

    //     return back()->with('success', 'Habitación cambiada correctamente.');
    // }
}
