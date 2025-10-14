<?php

namespace App\Http\Controllers;

use App\Models\Reservas;
use App\Models\Huespedes;
use App\Models\Habitaciones;
use App\Models\Asignaciones_habitacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReservaController extends Controller
{
    public function create()
    {
        // Huespedes + datos de personas (para mostrar DNI en el <select>)
        $huespedes = Huespedes::leftJoin('personas', 'personas.huesped_id', '=', 'huespedes.id')
            ->orderBy('personas.apellido')
            ->orderBy('personas.nombre')
            ->get([
                'huespedes.id',
                'huespedes.email',
                'huespedes.telefono',
                'personas.nombre',
                'personas.apellido',
                'personas.documento',
                DB::raw("TRIM(CONCAT(COALESCE(personas.apellido,''), ', ', COALESCE(personas.nombre,''), ' (DNI: ', COALESCE(personas.documento,''), ')')) AS display"),
            ]);

        $habitaciones = Habitaciones::select('id', 'numero', 'tipo', 'estado_actual')
            ->orderBy('numero')
            ->get();

        return Inertia::render('Reservas/Create', [
            'huespedes'    => $huespedes,
            'habitaciones' => $habitaciones,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'huesped_id'     => ['required', 'exists:huespedes,id'],
            'habitacion_id'  => ['required', 'exists:habitaciones,id'],
            'fecha_checkin'  => ['required', 'date', 'before:fecha_checkout'],
            'fecha_checkout' => ['required', 'date', 'after:fecha_checkin'],
            'estado'         => ['nullable', 'in:pendiente,checkin,checkout,cancelada'],
        ], [
            'huesped_id.required'     => 'Seleccioná un huésped.',
            'habitacion_id.required'  => 'Seleccioná una habitación.',
            'fecha_checkin.before'    => 'El check-in debe ser anterior al check-out.',
            'fecha_checkout.after'    => 'El check-out debe ser posterior al check-in.',
        ]);

        // Disponibilidad: hay conflicto si (nuevo_inicio < existente_fin/null∞) y (nuevo_fin > existente_inicio)
        $conflicto = Asignaciones_habitacion::where('habitacion_id', $request->habitacion_id)
            ->where('fecha_inicio', '<', $request->fecha_checkout)
            ->where(function ($q) use ($request) {
                $q->whereNull('fecha_fin')
                    ->orWhere('fecha_fin', '>', $request->fecha_checkin);
            })
            ->exists();

        if ($conflicto) {
            return back()
                ->withErrors(['habitacion_id' => 'La habitación no está disponible en ese rango.'])
                ->withInput();
        }

        DB::transaction(function () use ($request) {
            // 1) Crear la reserva (por defecto en 'pendiente')
            $reserva = Reservas::create([
                'huesped_id'     => $request->huesped_id,
                'fecha_checkin'  => $request->fecha_checkin,
                'fecha_checkout' => $request->fecha_checkout,
                'estado'         => $request->estado ?? 'pendiente',
                'usuario_id'     => Auth::id(),
                'fecha_reserva'  => now(), // si tenés esta columna en la tabla
                // NOTA: no guardamos habitacion_id en reservas si tu esquema no lo tiene
            ]);

            // 2) Crear la asignación inicial VIGENTE (fecha_fin = NULL)
            Asignaciones_habitacion::create([
                'reserva_id'    => $reserva->id,
                'habitacion_id' => $request->habitacion_id,
                'fecha_inicio'  => $request->fecha_checkin,
                'fecha_fin'     => null, // se cerrará en el checkout
                'motivo_cambio' => 'Asignación inicial',
            ]);
        });

        return redirect()->route('reservas.index')->with('success', 'Reserva registrada.');
    }

    // Endpoint para listar habitaciones disponibles según rango
    public function disponibles(Request $request)
    {
        $request->validate([
            'check_in'  => ['required', 'date'],
            'check_out' => ['required', 'date', 'after:check_in'],
        ]);

        $ocupadas = Asignaciones_habitacion::select('habitacion_id')
            ->where('fecha_inicio', '<', $request->check_out)
            ->where(function ($q) use ($request) {
                $q->whereNull('fecha_fin')
                    ->orWhere('fecha_fin', '>', $request->check_in);
            });

        $habitaciones = Habitaciones::whereNotIn('id', $ocupadas)
            ->orderBy('numero')
            ->get(['id', 'numero', 'tipo', 'estado_actual']);

        return response()->json($habitaciones);
    }

    public function buscarHuespedes(Request $request)
    {
        $term = trim($request->get('q', ''));

        $q = Huespedes::leftJoin('personas', 'personas.huesped_id', '=', 'huespedes.id')
            ->select(
                'huespedes.id',
                'personas.nombre',
                'personas.apellido',
                'personas.documento',
                DB::raw("TRIM(CONCAT(COALESCE(personas.apellido,''), ', ', COALESCE(personas.nombre,''), ' (DNI: ', COALESCE(personas.documento,''), ')')) AS display")
            );

        if ($term !== '') {
            $q->where(function ($w) use ($term) {
                $w->where('personas.apellido', 'like', "%{$term}%")
                    ->orWhere('personas.nombre', 'like', "%{$term}%")
                    ->orWhere('personas.documento', 'like', "%{$term}%");
            });
        }

        return response()->json($q->orderBy('personas.apellido')->limit(20)->get());
    }

    public function terminarLimpieza(Habitaciones $habitacion)
    {
        $habitacion->update(['estado_actual' => 'disponible']);
        return back()->with('success', 'Limpieza finalizada. Habitación disponible.');
    }


    public function index()
    {
        // Subquery: última asignación por reserva (MAX(fecha_inicio))
        $ultimaAsignacion = DB::table('asignaciones_habitacion')
            ->select('reserva_id', DB::raw('MAX(fecha_inicio) AS fi'))
            ->groupBy('reserva_id');

        // Join a la asignación concreta y a habitaciones para traer numero/tipo
        $reservas = DB::table('reservas')
            ->leftJoin('huespedes', 'huespedes.id', '=', 'reservas.huesped_id')
            ->leftJoin('personas', 'personas.huesped_id', '=', 'huespedes.id')
            ->leftJoinSub($ultimaAsignacion, 'ua', function ($join) {
                $join->on('ua.reserva_id', '=', 'reservas.id');
            })
            ->leftJoin('asignaciones_habitacion as ah', function ($join) {
                $join->on('ah.reserva_id', '=', 'ua.reserva_id')
                    ->on('ah.fecha_inicio', '=', 'ua.fi');
            })
            ->leftJoin('habitaciones as h', 'h.id', '=', 'ah.habitacion_id')
            ->orderByDesc('reservas.id')
            ->get([
                'reservas.id',
                'reservas.fecha_checkin',
                'reservas.fecha_checkout',
                'reservas.estado',
                DB::raw("TRIM(CONCAT(COALESCE(personas.apellido,''), ' ', COALESCE(personas.nombre,''))) AS huesped_nombre"),
                'h.numero as habitacion_numero',
                'h.tipo   as habitacion_tipo',
            ]);

        return inertia('Reservas/Index', ['reservas' => $reservas]);
    }
}
