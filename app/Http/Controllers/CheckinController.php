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
        // ref_mode: plan => usa fecha_checkin de la reserva (default)
        //           hoy  => usa la fecha actual
        $mode = request('ref', 'plan'); // ?ref=plan | ?ref=hoy
        $hoy  = now()->toDateString();

        $query = DB::table('reservas as r')
            ->where('r.estado', 'pendiente')
            ->join('huespedes as hu', 'hu.id', '=', 'r.huesped_id')
            ->leftJoin('personas as p', 'p.huesped_id', '=', 'hu.id')
            ->leftJoin('empresas as e', 'e.huesped_id', '=', 'hu.id')
            ->join('asignaciones_habitacion as ah', 'ah.reserva_id', '=', 'r.id')
            ->join('habitaciones as h', 'h.id', '=', 'ah.habitacion_id')
            ->orderByDesc('r.id')
            ->orderBy('h.numero')
            ->select([
                'r.id              as reserva_id',
                'r.fecha_reserva',
                'r.fecha_checkin',
                'r.fecha_checkout',
                'r.estado',
                'ah.id             as asignacion_id',
                'ah.fecha_inicio   as checkin_det',
                'ah.fecha_fin      as checkout_det',
                'h.id              as habitacion_id',
                'h.numero          as habitacion_numero',
                'h.estado_actual   as habitacion_estado',
                DB::raw("
                CASE 
                    WHEN hu.tipo_huesped = 'persona' 
                        THEN TRIM(CONCAT(COALESCE(p.apellido,''), ' ', COALESCE(p.nombre,'')))
                    WHEN hu.tipo_huesped = 'empresa' 
                        THEN COALESCE(e.razon_social, '')
                    ELSE '—'
                END AS huesped_nombre
            "),
            ]);

        if ($mode === 'hoy') {
            // VIGENTES HOY
            $query->whereDate('ah.fecha_inicio', '<=', $hoy)
                ->where(function ($q) use ($hoy) {
                    $q->whereNull('ah.fecha_fin')
                        ->orWhereDate('ah.fecha_fin', '>=', $hoy);
                });
        } else {
            // VIGENTES A LA FECHA PLANIFICADA (de la reserva)
            // si alguna reserva no tiene fecha_checkin, usamos el inicio de asignación
            $query->whereColumn('ah.fecha_inicio', '<=', DB::raw('COALESCE(r.fecha_checkin, ah.fecha_inicio)'))
                ->where(function ($q) {
                    $q->whereNull('ah.fecha_fin')
                        ->orWhereColumn('ah.fecha_fin', '>=', DB::raw('COALESCE(r.fecha_checkin, ah.fecha_inicio)'));
                });
        }

        $rows = $query->get();

        return Inertia::render('Reservas/Checkin', [
            'reservas' => $rows,
            'ref_mode' => $mode, // para que el front sepa en qué modo está
        ]);
    }
    public function checkin(Request $request, Reservas $reserva)
    {
        if ($reserva->estado !== 'pendiente') {
            return back()->withErrors(['error' => 'La reserva no está en estado pendiente.']);
        }

        // Tomamos HOY como referencia operacional
        $hoy = now()->toDateString();

        // Elegí la referencia: planificada o “hoy”
        // $fechaRef = now()->toDateString(); // <- si preferís operar por el día actual
        // Buscar asignación vigente a HOY (no solo abierta)
        // $hoy = now()->toDateString(); 


        // Todas las asignaciones vigentes HOY
        $vigentes = $reserva->asignacionesVigentesA($hoy)
            ->with('habitacion:id,numero,estado_actual')
            ->orderBy('fecha_inicio')
            ->get();

        if ($vigentes->isEmpty()) {
            return back()->withErrors([
                'error' => 'La reserva no tiene habitaciones vigentes para hoy.'
            ]);
        }

        // 1) VALIDACIÓN: Estado de habitación
        $bloqueos = [];
        foreach ($vigentes as $a) {
            $hab = $a->habitacion;
            if (!$hab) {
                $bloqueos[] = "Asignación {$a->id}: habitación inexistente.";
                continue;
            }
            if (in_array($hab->estado_actual, ['limpieza', 'mantenimiento', 'ocupada'])) {
                $bloqueos[] = "Habitación {$hab->numero} en {$hab->estado_actual}.";
            }
        }
        if ($bloqueos) {
            return back()->withErrors(['error' => implode(' ', $bloqueos)]);
        }

        DB::transaction(function () use ($reserva, $vigentes, $hoy) {
            // 2) (Opcional) AUTO-CERRAR asignaciones abiertas si pisan con otra posterior
            foreach ($vigentes as $a) {
                if (!is_null($a->fecha_fin)) {
                    continue; // ya tiene fin
                }

                // Buscá la próxima asignación de ESTA reserva y ESTA habitación posterior a hoy
                $siguiente = Asignaciones_habitacion::where('reserva_id', $reserva->id)
                    ->where('habitacion_id', $a->habitacion_id)
                    ->whereDate('fecha_inicio', '>', $hoy)
                    ->orderBy('fecha_inicio')
                    ->first();

                if ($siguiente) {
                    // Cerramos la vigente el día anterior al próximo inicio
                    $nuevoFin = \Carbon\Carbon::parse($siguiente->fecha_inicio)->subDay()->toDateString();
                    if (is_null($a->fecha_fin) || $a->fecha_fin > $nuevoFin) {
                        $a->fecha_fin = $nuevoFin;
                        $a->motivo_cambio = $a->motivo_cambio ?: 'Auto-cierre por solapamiento';
                        $a->save();
                    }
                }
            }

            // 3) Marcar TODAS las habitaciones vigentes como "ocupada"
            $habIds = $vigentes->pluck('habitacion_id')->unique()->values();
            Habitaciones::whereIn('id', $habIds)->update(['estado_actual' => 'ocupada']);

            // 4) Actualizar la reserva a check-in y fecha real
            $reserva->update([
                'estado'        => 'checkin',
                'fecha_checkin' => $hoy,
            ]);

            // 5) Marcar motivo en las asignaciones (si querés)
            foreach ($vigentes as $a) {
                if (empty($a->motivo_cambio)) {
                    $a->motivo_cambio = 'Check-in';
                    $a->save();
                }
            }
        });

        return redirect()
            ->route('checkin.index')
            ->with('success', 'Check-in realizado. Habitaciones marcadas como ocupadas.');
    }
}
