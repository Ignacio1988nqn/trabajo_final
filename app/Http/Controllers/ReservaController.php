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
        // Huespedes + datos de personas (incluye documento)
        $huespedes = Huespedes::leftJoin('personas', 'personas.huesped_id', '=', 'huespedes.id')
            ->orderBy('personas.apellido')
            ->orderBy('personas.nombre')
            ->get([
                'huespedes.id',
                'huespedes.email',
                'huespedes.telefono',
                'personas.nombre',
                'personas.apellido',
                'personas.documento', // <- acá va el documento
                // Un campo “display” listo para mostrar en el <select>
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
            'estado'         => ['nullable', 'in:reservada,checkin,checkout,cancelada'],
        ], [
            'huesped_id.required'     => 'Seleccioná un huésped.',
            'habitacion_id.required'  => 'Seleccioná una habitación.',
            'fecha_checkin.before'    => 'El check-in debe ser anterior al check-out.',
            'fecha_checkout.after'    => 'El check-out debe ser posterior al check-in.',
        ]);

        // Disponibilidad: se solapa si (nuevo_in < ex_fin) y (nuevo_fin > ex_inicio)
        $conflicto = Asignaciones_habitacion::where('habitacion_id', $request->habitacion_id)
            ->where(function ($q) use ($request) {
                $q->where('fecha_inicio', '<', $request->fecha_checkout)
                    ->where('fecha_fin',    '>', $request->fecha_checkin);
            })
            ->exists();

        if ($conflicto) {
            return back()
                ->withErrors(['habitacion_id' => 'La habitación no está disponible en ese rango.'])
                ->withInput();
        }

        DB::transaction(function () use ($request) {
            $reserva = Reservas::create([
                'huesped_id'     => $request->huesped_id,
                'fecha_checkin'  => $request->fecha_checkin,
                'fecha_checkout' => $request->fecha_checkout,
                'estado'         => $request->estado ?? 'checkin', // o 'reservada'
                'usuario_id'     => Auth::id(),
                // 'fecha_reserva' => now(), // si tenés ese campo y no usás timestamps
            ]);

            Asignaciones_habitacion::create([
                'reserva_id'    => $reserva->id,
                'habitacion_id' => $request->habitacion_id,
                'fecha_inicio'  => $request->fecha_checkin,
                'fecha_fin'     => $request->fecha_checkout,
                'motivo_cambio' => null,
            ]);
        });

        return redirect()->route('reservas.index')->with('success', 'Check-in/Reserva registrada.');
    }

    // Opcional: endpoint para listar habitaciones disponibles según rango
    public function disponibles(Request $request)
    {
        $request->validate([
            'check_in'  => ['required', 'date'],
            'check_out' => ['required', 'date', 'after:check_in'],
        ]);

        $ocupadas = Asignaciones_habitacion::select('habitacion_id')
            ->where(function ($q) use ($request) {
                $q->where('fecha_inicio', '<', $request->check_out)
                    ->where('fecha_fin',    '>', $request->check_in);
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

    public function index()
    {
        $reservas = \DB::table('reservas')
            ->leftJoin('huespedes', 'huespedes.id', '=', 'reservas.huesped_id')
            ->leftJoin('personas', 'personas.huesped_id', '=', 'huespedes.id')
            ->orderByDesc('reservas.id')
            ->get([
                'reservas.id',
                'reservas.fecha_checkin',
                'reservas.fecha_checkout',
                'reservas.estado',
                \DB::raw("TRIM(CONCAT(COALESCE(personas.apellido,''), ' ', COALESCE(personas.nombre,''))) AS huesped_nombre"),
            ]);

        return inertia('Reservas/Index', ['reservas' => $reservas]);
    }
}
