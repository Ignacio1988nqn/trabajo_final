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
        if (!in_array($reserva->estado, ['checkin', 'pendiente'])) {
            return back()->withErrors(['error' => 'La reserva no está en un estado válido para check-out.']);
        }

        // 1) Candidatas de referencia (en orden)
        $candidatas = collect([
            optional($reserva->fecha_checkout)->toDateString(),
            optional($reserva->fecha_checkin)->toDateString(),
            now()->toDateString(),
        ])->filter()->unique()->values();

        // 2) Intentamos cerrar VIGENTES para la primera ref que aplique
        $vigentes = collect();
        $refElegida = null;
        foreach ($candidatas as $ref) {
            $tmp = $reserva->asignacionesVigentes($ref)->get();
            if ($tmp->isNotEmpty()) {
                $vigentes   = $tmp;
                $refElegida = $ref;
                break;
            }
        }

        // 3) Si hay vigentes, cerramos esas; si no, cerramos TODAS (cierre anticipado)
        $aCerrar = $vigentes->isNotEmpty()
            ? $vigentes
            : $reserva->asignaciones()->get(); // ← cierre forzado de todas las asignaciones

        if ($aCerrar->isEmpty()) {
            // ahora sí es un error real: la reserva no tiene asignaciones
            return back()->withErrors(['error' => 'La reserva no tiene asignaciones registradas.']);
        }

        // 4) Fecha de cierre efectiva:
        //    - Si hoy cae dentro de alguna vigente, cerramos con HOY.
        //    - Si no, usamos la mejor referencia disponible (refElegida o, en cierre forzado, hoy).
        $hoy = now()->toDateString();
        $hoyDentro = $aCerrar->contains(function ($a) use ($hoy) {
            return $a->fecha_inicio <= $hoy && (is_null($a->fecha_fin) || $a->fecha_fin >= $hoy);
        });
        $cerrarCon = $hoyDentro ? $hoy : ($refElegida ?? $hoy);

        DB::transaction(function () use ($reserva, $aCerrar, $cerrarCon) {
            // 4.a) Cerrar asignaciones: si el cierre es anterior al inicio, usamos el inicio para no “invertir” el rango
            foreach ($aCerrar as $a) {
                $fin = $cerrarCon;
                if ($fin < $a->fecha_inicio) {
                    $fin = $a->fecha_inicio; // cierre anticipado antes de iniciar: que termine el mismo día que empezaba
                }
                $a->fecha_fin = $fin;
                if (empty($a->motivo_cambio)) {
                    $a->motivo_cambio = 'Check-out';
                }
                $a->save();
            }

            // 4.b) Marcar habitaciones: solo las que estén ocupadas pasan a limpieza
            $habitacionIds = $aCerrar->pluck('habitacion_id')->unique()->values();
            \App\Models\Habitaciones::whereIn('id', $habitacionIds)
                ->where('estado_actual', 'ocupada')
                ->update(['estado_actual' => 'limpieza']);

            // 4.c) Actualizar la reserva
            $reserva->update([
                'estado'         => 'checkout',
                'fecha_checkout' => $cerrarCon,
            ]);
        });

        return back()->with('success', 'Check-out realizado. Habitaciones enviadas a limpieza.');
    }
}
