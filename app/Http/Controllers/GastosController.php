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
        return Inertia::render('Gastos/GastosIndex', [
            'reservas' => Reservas::with([
                'huesped.personas',
                'huesped.empresas'
            ])
                ->orderByDesc('id')
                ->get()
                ->map(function ($reserva) {
                    return [
                        'id' => $reserva->id,
                        'cliente' => $reserva->huesped
                            ? ($reserva->huesped->tipo_huesped === 'persona'
                                ? ($reserva->huesped->personas?->apellido . ' ' . $reserva->huesped->personas?->nombre)
                                : $reserva->huesped->empresas?->razon_social)
                            : 'Huésped no encontrado',
                        'fecha_inicio' => $reserva->fecha_checkin,
                    ];
                }),
        ]);
    }

    public function show(ReservaDetalle $detalle)
    {
        $detalle->load([
            'reserva.huesped.personas',
            'reserva.huesped.empresas',
            'asignacionesHabitacion.habitacion',
            'gastos.item',
        ]);

        return inertia('Gastos/GastosShow', [
            'detalleReserva' => $detalle,
            'gastos' => $detalle->gastos,
        ]);
    }


    public function create(ReservaDetalle $detalle)
    {
        $detalle->load([
            'reserva.huesped.personas',
            'reserva.huesped.empresas',
            'asignacionesHabitacion.habitacion',
        ]);

        if ($detalle->estado !== 'checkin') {
            return redirect()->back()->with('error', 'Solo se pueden cargar gastos en estado check-in');
        }

        $items = GastoItems::orderBy('nombre')->get(['id', 'nombre', 'precio', 'tipo', 'stock']);
        $tipos = GastoItems::select('tipo')->distinct()->pluck('tipo');

        return Inertia::render('Gastos/Create', [
            'detalleReserva' => $detalle,
            'items'          => $items,
            'tipos'          => $tipos,
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
            ->join('asignaciones_habitacion as ah', 'ah.reserva_detalle_id', '=', 'rd.id')
            ->join('habitaciones as h', 'h.id', '=', 'ah.habitacion_id')
            ->where('rd.reserva_id', $reservaId)
            ->select(
                'rd.id as detalle_id',
                'h.id as habitacion_id',
                'h.numero as habitacion_numero',
                'ah.id as asignacion_id',
                'rd.fecha_checkin',
                'rd.fecha_checkout',
                'rd.estado',
                'ah.fecha_inicio as fecha_inicio_asignada',
                'ah.fecha_fin as fecha_fin_asignada'
            )
            ->get();

        $reserva = Reservas::with(['huesped.personas', 'huesped.empresas'])
            ->find($reservaId);

        return Inertia::render('Gastos/Asignaciones', [
            'reserva'      => $reserva,
            'asignaciones' => $asignaciones
        ]);
    }
}
