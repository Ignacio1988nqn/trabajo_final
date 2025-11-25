<?php

namespace App\Http\Controllers;

use App\Models\Reservas;
use App\Models\Habitaciones;
use App\Models\Asignaciones_habitacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\ReservaDetalle;

class CheckinController extends Controller
{

    public function index()
    {
        $mode = request('ref', 'hoy'); // ?ref=plan | ?ref=hoy
        $hoy  = now()->toDateString();

        $query = DB::table('reservas as r')
            ->join('huespedes as hu', 'hu.id', '=', 'r.huesped_id')
            ->leftJoin('personas as p', 'p.huesped_id', '=', 'hu.id')
            ->leftJoin('empresas as e', 'e.huesped_id', '=', 'hu.id')

            ->join('reserva_detalles as rd', 'rd.reserva_id', '=', 'r.id')

            ->join('asignaciones_habitacion as ah', 'ah.reserva_detalle_id', '=', 'rd.id')

            ->join('habitaciones as h', 'h.id', '=', 'ah.habitacion_id')
            ->where('rd.estado', 'pendiente')
            ->orderByDesc('r.id')
            ->orderBy('h.numero')

            ->select([
                'r.id               as reserva_id',
                'rd.id              as detalle_id',
                'rd.estado          as estado',
                'r.estado           as reserva_estado',
                'rd.estado          as detalle_estado',

                'r.fecha_reserva',
                'rd.fecha_checkin   as reserva_checkin',
                'rd.fecha_checkout  as reserva_checkout',

                'ah.id              as asignacion_id',
                'ah.fecha_inicio    as checkin_det',
                'ah.fecha_fin       as checkout_det',

                'h.id               as habitacion_id',
                'h.numero           as habitacion_numero',
                'h.estado_actual    as habitacion_estado',

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


        // ===============================
        // FILTRO: CHECKIN HOY (modo real)
        // ===============================
        if ($mode === 'hoy') {
            $query->whereDate('rd.fecha_checkin', '<=', $hoy)
                ->where(function ($q) use ($hoy) {
                    $q->whereNull('rd.fecha_checkout')
                        ->orWhereDate('rd.fecha_checkout', '>=', $hoy);
                });

            // =================================================
            // FILTRO: CHECKIN PLANIFICADO (usa fechas del detalle)
            // =================================================
        } else {
            $query->whereDate('rd.fecha_checkin', '<=', DB::raw('COALESCE(rd.fecha_checkin, ah.fecha_inicio)'))
                ->where(function ($q) {
                    $q->whereNull('rd.fecha_checkout')
                        ->orWhereColumn('rd.fecha_checkout', '>=', DB::raw('COALESCE(rd.fecha_checkin, ah.fecha_inicio)'));
                });
        }

        $rows = $query->get();

        return Inertia::render('Reservas/Checkin', [
            'reservas' => $rows,
            'ref_mode' => $mode,
        ]);
    }

    public function checkin($detalleId)
    {
        $detalle = ReservaDetalle::findOrFail($detalleId);

        if ($detalle->estado !== 'pendiente') {
            return back()->withErrors([
                'error' => 'El detalle ya no está pendiente.'
            ]);
        }

        // Obtener la asignación y la habitación
        $asig = $detalle->asignacionesHabitacion()->first();

        if (!$asig) {
            return back()->withErrors([
                'error' => 'No hay habitación asignada.'
            ]);
        }

        $habitacion = $asig->habitacion;

        if ($habitacion->estado_actual === 'ocupada') {
            return back()->withErrors([
                'error' => "La habitación {$habitacion->numero} ya está ocupada. \n Por favor verifica CheckOuts pendientes."
            ]);
        }

        // Todo bien → hacer check-in
        $detalle->update(['estado' => 'checkin']);

        // Marcar como ocupada
        $habitacion->update(['estado_actual' => 'ocupada']);

        return redirect()
            ->route('checkin.index')
            ->with('success', 'Check-in realizado.');
    }
}
