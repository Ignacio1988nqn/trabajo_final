<?php

namespace App\Http\Controllers;

use App\Models\Reservas;
use App\Models\Habitaciones;
use App\Models\Asignaciones_habitacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CheckinController extends Controller
{
    public function index()
    {
        // Cargar huésped + asignación vigente + habitación
        $reservas = Reservas::with([
            'huesped.personas',
            'huesped.empresas',
            'asignacionVigente.habitacion',
        ])
            ->where('estado', 'pendiente')
            ->get()
            ->map(function ($r) {
                // nombre a mostrar
                $huespedNombre = 'Huésped no encontrado';
                if ($r->huesped) {
                    if ($r->huesped->personas) {
                        $huespedNombre = $r->huesped->personas->nombre . ' ' . $r->huesped->personas->apellido;
                    } elseif ($r->huesped->empresas) {
                        $huespedNombre = $r->huesped->empresas->razon_social ?? 'Sin nombre';
                    }
                }

                return [
                    'id'              => $r->id,
                    'huesped'         => $huespedNombre,
                    'fecha_reserva'   => optional($r->fecha_reserva)->format('d/m/Y H:i'),
                    'fecha_checkin'   => optional($r->fecha_checkin)->format('d/m/Y') ?: 'No asignada',
                    'fecha_checkout'  => optional($r->fecha_checkout)->format('d/m/Y') ?: 'No asignada',
                    'estado'          => $r->estado,
                    // útil para la vista: saber si ya tiene asignación
                    'habitacion'      => optional(optional($r->asignacionVigente)->habitacion)->numero,
                ];
            });

        return Inertia::render('Reservas/Checkin', compact('reservas'));
    }

    public function checkin(Request $request, Reservas $reserva)
    {
        // 1) Debe estar pendiente
        if ($reserva->estado !== 'pendiente') {
            return back()->withErrors(['error' => 'La reserva no está en estado pendiente.']);
        }

        // 2) Debe existir una ASIGNACIÓN vigente (no mirar habitacion_id en reservas)
        $asignacion = $reserva->asignaciones()
            ->whereNull('fecha_fin')          // vigente
            ->latest('fecha_inicio')
            ->with('habitacion')
            ->first();

        if (!$asignacion) {
            return back()->withErrors(['error' => 'La reserva no tiene una habitación asignada.']);
            // o redirigir a ruta para asignar: return redirect()->route('reservas.asignar', $reserva->id);
        }

        // 3) Opcional: verificar estado de la habitación
        if ($asignacion->habitacion && $asignacion->habitacion->estado_actual !== 'disponible') {
            return back()->withErrors(['error' => 'La habitación asignada no está disponible.']);
        }

        // 4) Transacción de check-in
        DB::transaction(function () use ($reserva, $asignacion) {
            // marcar habitación ocupada
            Habitaciones::where('id', $asignacion->habitacion_id)
                ->update(['estado_actual' => 'ocupada']);

            // actualizar reserva
            $reserva->update([
                'estado'        => 'checkin',              // o 'en_estadia'
                'fecha_checkin' => now()->toDateString(),  // registra momento real
            ]);

            // si querés, registrar motivo en la asignación vigente
            if (empty($asignacion->motivo_cambio)) {
                $asignacion->motivo_cambio = 'Check-in';
                $asignacion->save();
            }
        });

        return redirect()
            ->route('checkin.index')
            ->with('success', 'Check-in realizado y habitación marcada como ocupada.');
    }
}
