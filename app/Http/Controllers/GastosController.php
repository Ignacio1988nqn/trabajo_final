<?php

namespace App\Http\Controllers;

use App\Models\Reservas;
use App\Models\Gastos;
use App\Models\GastoItems;
use App\Models\Asignaciones_habitacion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Models\ReservaDetalle;

class GastosController extends Controller
{

    public function index()
    {
        $reservas = Reservas::with([
            'huesped.personas',
            'huesped.empresas',
            'detalles' => function ($q) {
                $q->select('id', 'reserva_id', 'estado');
            }
        ])
            ->orderByDesc('id')
            ->get();

        return Inertia::render('Gastos/GastosIndex', [
            'reservas' => $reservas->map(function ($reserva) {

                $habitacionesConEstado = collect();

                foreach ($reserva->detalles as $detalle) {
                    $asignacion = Asignaciones_habitacion::vigente()
                        ->where('reserva_detalle_id', $detalle->id)
                        ->orderByDesc('id')
                        ->first();

                    if ($asignacion?->habitacion) {
                        $habitacionesConEstado->push([
                            'numero' => $asignacion->habitacion->numero,
                            'estado' => $detalle->estado,
                        ]);
                    }
                }

                $habitacionTexto = $habitacionesConEstado->pluck('numero')->implode(', ');
                $habitacionesData = $habitacionesConEstado->toArray();

                $fechaInicio = $reserva->fecha_checkin
                    ? \Carbon\Carbon::parse($reserva->fecha_checkin)->format('d \d\e F')
                    : null;

                $fechaFin = $reserva->fecha_checkout
                    ? \Carbon\Carbon::parse($reserva->fecha_checkout)->format('d \d\e F')
                    : '—';

                $totalGastos = $reserva->gastos()->sum('monto') ?? 0;

                return [
                    'id'                 => $reserva->id,
                    'cliente'            => $reserva->huesped
                        ? ($reserva->huesped->tipo_huesped === 'persona'
                            ? trim(($reserva->huesped->personas?->apellido ?? '') . ' ' . ($reserva->huesped->personas?->nombre ?? ''))
                            : ($reserva->huesped->empresas?->razon_social ?? 'Sin razón social'))
                        : 'Huésped no encontrado',
                    'habitacion_numero'  => $habitacionTexto ?: '—',
                    'habitaciones_data'  => $habitacionesData,
                    'fecha_inicio'       => $fechaInicio,
                    'fecha_fin'          => $fechaFin,
                    'estado'             => $reserva->estado ?? 'desconocido',
                    'total_gastos'       => $totalGastos,
                ];
            }),
        ]);
    }

    public function show(ReservaDetalle $detalle)
    {
        $detalle->load([
            'reserva.huesped.personas',
            'reserva.huesped.empresas',
            'gastos.item',
        ]);

        $ultimaAsignacion = DB::table('asignaciones_habitacion')
            ->where('reserva_detalle_id', $detalle->id)
            ->orderByDesc('id')
            ->first();

        $habitacionActual = $ultimaAsignacion
            ? DB::table('habitaciones')->where('id', $ultimaAsignacion->habitacion_id)->first()
            : null;

        return inertia('Gastos/GastosShow', [
            'detalleReserva' => $detalle,
            'gastos'         => $detalle->gastos,
            'habitacionActual' => $habitacionActual,
            'checkinDetalle'   => $detalle->fecha_checkin,
        ]);
    }
    public function create(ReservaDetalle $detalle)
    {
        $detalle->load([
            'reserva.huesped.personas',
            'reserva.huesped.empresas',
        ]);

        if ($detalle->estado !== 'checkin') {
            return redirect()->back()->with('error', 'Solo se pueden cargar gastos en estado check-in');
        }

        $ultimaAsignacion = DB::table('asignaciones_habitacion')
            ->where('reserva_detalle_id', $detalle->id)
            ->orderByDesc('id')
            ->first();

        $habitacionActual = $ultimaAsignacion
            ? DB::table('habitaciones')->where('id', $ultimaAsignacion->habitacion_id)->first()
            : null;

        $items = GastoItems::orderBy('nombre')->get(['id', 'nombre', 'precio', 'tipo', 'stock']);
        $tipos = GastoItems::select('tipo')->distinct()->pluck('tipo');

        return Inertia::render('Gastos/Create', [
            'detalleReserva'   => $detalle,
            'habitacionActual' => $habitacionActual,
            'items'            => $items,
            'tipos'            => $tipos,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'reserva_detalle_id' => 'required|exists:reserva_detalles,id',
            'gasto_item_id'      => 'required|exists:gasto_items,id',
            'cantidad'           => 'required|integer|min:1',
            'fecha'              => 'required|date',
        ]);

        $detalle = ReservaDetalle::findOrFail($request->reserva_detalle_id);
        $item    = GastoItems::findOrFail($request->gasto_item_id);

        if ($item->stock !== null && $request->cantidad > $item->stock) {
            return back()->withErrors(['cantidad' => "Stock insuficiente. Disponible: {$item->stock}"]);
        }

        for ($i = 0; $i < $request->cantidad; $i++) {
            Gastos::create([
                'reserva_id'         => $detalle->reserva_id,
                'reserva_detalle_id' => $detalle->id,
                'gasto_item_id'      => $item->id,
                'descripcion'        => $item->nombre,
                'monto'              => $item->precio,
                'fecha'              => $request->fecha,
                'tipo'               => $item->tipo,
            ]);
        }

        if ($item->stock !== null) {
            $item->decrement('stock', $request->cantidad);
        }

        return redirect()
            ->route('gastos.show', $detalle->id)
            ->with('success', "¡Perfecto! Se cargaron {$request->cantidad} × {$item->nombre}");
    }

    public function asignaciones($reservaId)
    {

        $asignaciones = DB::table('reserva_detalles as rd')
            ->leftJoin('asignaciones_habitacion as ah', function ($join) {
                $join->on('ah.reserva_detalle_id', '=', 'rd.id')
                    ->whereRaw('ah.id = (
                 SELECT MAX(ah2.id) 
                 FROM asignaciones_habitacion ah2 
                 WHERE ah2.reserva_detalle_id = rd.id
             )');
            })
            ->leftJoin('habitaciones as h', 'h.id', '=', 'ah.habitacion_id')
            ->leftJoin('gastos as g', 'g.reserva_detalle_id', '=', 'rd.id')
            ->where('rd.reserva_id', $reservaId)
            ->groupBy(
                'rd.id',
                'h.numero',
                'rd.fecha_checkin',
                'rd.fecha_checkout',
                'rd.estado'
            )
            ->select(
                'rd.id as detalle_id',
                'h.numero as habitacion_numero',
                'rd.fecha_checkin',
                'rd.fecha_checkout',
                'rd.estado',

                DB::raw('COALESCE(SUM(g.monto), 0) as total_gastos')
            )
            ->get();

        $reserva = Reservas::with(['huesped.personas', 'huesped.empresas'])
            ->find($reservaId);

        return Inertia::render('Gastos/Asignaciones', [
            'reserva'      => $reserva,
            'asignaciones' => $asignaciones,

        ]);
    }
}
