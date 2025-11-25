<?php

namespace App\Http\Controllers;

use App\Models\Reservas;
use App\Models\Habitaciones;
use App\Models\Asignaciones_habitacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Models\ReservaDetalle;

class CheckoutController extends Controller
{
    public function index()
    {
        $rows = DB::table('reservas as r')
            ->join('huespedes as hu', 'hu.id', '=', 'r.huesped_id')
            ->leftJoin('personas as p', 'p.huesped_id', '=', 'hu.id')
            ->leftJoin('empresas as e', 'e.huesped_id', '=', 'hu.id')
            ->join('reserva_detalles as rd', 'rd.reserva_id', '=', 'r.id')
            ->leftJoin('asignaciones_habitacion as ah', function ($join) {
                $join->on('ah.reserva_detalle_id', '=', 'rd.id')
                    ->whereRaw('ah.id = (SELECT MAX(ah2.id) FROM asignaciones_habitacion ah2 WHERE ah2.reserva_detalle_id = rd.id)');
            })
            ->leftJoin('habitaciones as h', 'h.id', '=', 'ah.habitacion_id')
            ->where('rd.estado', 'checkin')
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
                'ah.fecha_inicio    as fecha_checkin_real',
                'ah.fecha_fin       as checkout_asignado',
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
            ])
            ->get();

        return Inertia::render('Reservas/Checkout', [
            'reservas' => $rows,
        ]);
    }

    public function checkout($detalleId)
    {
        $detalle = ReservaDetalle::findOrFail($detalleId);

        if ($detalle->estado !== 'checkin') {
            return back()->withErrors([
                'error' => 'Esta reserva no está en check-in.'
            ]);
        }

        $hoy = now();
        $detalle->update([
            'estado'              => 'checkout',
            'fecha_checkout' => $hoy,
        ]);

        $asig = Asignaciones_habitacion::where('reserva_detalle_id', $detalle->id)
            ->orderByDesc('id')
            ->first();

        if ($asig) {
            $asig->update([
                'fecha_fin' => $hoy,
            ]);

            $asig->habitacion()->update([
                'estado_actual' => 'limpieza'
            ]);
        }

        return redirect()
            ->route('checkout.index')
            ->with('success', 'Check-out realizado correctamente.');
    }
}
