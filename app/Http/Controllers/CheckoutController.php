<?php

namespace App\Http\Controllers;

use App\Models\Reservas;
use App\Models\Habitaciones;
use App\Models\Asignaciones_habitacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class CheckoutController extends Controller
{
    public function index()
    {
        // Trae reservas actualmente en "checkin"
        $reservas = Reservas::with([
            'huesped.personas',
            'huesped.empresas',
            'asignaciones.habitacion'
        ])
            ->where('estado', 'checkin')
            ->get()
            ->map(function ($reserva) {
                $huespedNombre = 'Huésped no encontrado';
                if ($reserva->huesped) {
                    if ($reserva->huesped->personas) {
                        $huespedNombre = $reserva->huesped->personas->nombre . ' ' . $reserva->huesped->personas->apellido;
                    } elseif ($reserva->huesped->empresas) {
                        $huespedNombre = $reserva->huesped->empresas->razon_social ?? 'Sin nombre';
                    }
                }

                return [
                    'id'              => $reserva->id,
                    'huesped'         => $huespedNombre,
                    'fecha_reserva'   => optional($reserva->fecha_reserva)->format('d/m/Y H:i'),
                    'fecha_checkin'   => optional($reserva->fecha_checkin)->format('d/m/Y H:i') ?: 'No asignada',
                    'fecha_checkout'  => optional($reserva->fecha_checkout)->format('d/m/Y H:i') ?: 'No asignada',
                    'estado'          => $reserva->estado,
                ];
            });

        return Inertia::render('Reservas/Checkout', [
            'reservas' => $reservas,
        ]);
    }

    public function checkout(Request $request, Reservas $reserva)
    {
        if ($reserva->estado !== 'checkin') {
            return back()->withErrors(['error' => 'La reserva no está en estado check-in.']);
        }

        DB::transaction(function () use ($reserva) {
            // 1️⃣ Buscar la asignación vigente (sin fecha_fin)
            $asignacion = Asignaciones_habitacion::where('reserva_id', $reserva->id)
                ->whereNull('fecha_fin')
                ->latest('fecha_inicio')
                ->first();

            if ($asignacion) {
                // 2️⃣ Cerrar la asignación
                $asignacion->update([
                    'fecha_fin' => now(),
                    'motivo_cambio' => 'Check-out',
                ]);

                // 3️⃣ Marcar la habitación como “limpieza”
                Habitaciones::where('id', $asignacion->habitacion_id)
                    ->update(['estado_actual' => 'limpieza']);
            }

            // 4️⃣ Cambiar estado de reserva
            $reserva->update([
                'estado' => 'checkout',
                'fecha_checkout' => now(),
            ]);
        });

        return redirect()
            ->route('checkout.index')
            ->with('success', 'Check-out realizado. Habitación marcada como limpieza.');
    }
}
