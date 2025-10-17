<?php

namespace App\Http\Controllers;

use App\Models\Reservas;
use App\Models\Gastos;
use App\Models\GastoItems;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GastosController extends Controller
{
    public function index()
    {
        return Inertia::render('Gastos/GastosIndex', [
            'reservas' => Reservas::with(['huesped.personas', 'huesped.empresas'])->get()->map(function ($reserva) {
                return [
                    'id' => $reserva->id,
                    'cliente' => $reserva->huesped
                        ? ($reserva->huesped->tipo_huesped === 'persona'
                            ? ($reserva->huesped->personas ? $reserva->huesped->personas->nombre . ' ' . $reserva->huesped->personas->apellido : 'Sin nombre')
                            : ($reserva->huesped->empresas ? $reserva->huesped->empresas->razon_social : 'Sin razón social'))
                        : 'Huésped no encontrado',
                    'fecha_inicio' => $reserva->fecha_checkin,
                    'estado' => $reserva->estado,
                ];
            }),
        ]);
    }

    public function show(Reservas $reserva)
    {
        $reserva->load(['gastos.item']);

        return Inertia::render('Gastos/GastosShow', [
            'reserva' => [
                'id' => $reserva->id,
                'estado' => $reserva->estado,
            ],
            'gastos' => $reserva->gastos->map(function ($gasto) {
                return [
                    'id' => $gasto->id,
                    'descripcion' => $gasto->item?->nombre ?? 'Sin descripción',
                    'monto' => $gasto->monto ?? 0,
                    'fecha' => $gasto->fecha,
                    'tipo' => $gasto->item?->tipo ?? 'Sin tipo',
                ];
            }),
        ]);
    }

    public function create($reserva_id)
    {
        $reserva = Reservas::with('huesped')->findOrFail($reserva_id);
        if (!in_array($reserva->estado, ['pendiente', 'checkin'])) {
            return redirect()->back()->with('error', 'No se pueden cargar gastos a una reserva en estado ' . $reserva->estado);
        }

        $items = GastoItems::orderBy('nombre')->get(['id', 'nombre', 'precio', 'tipo']);
        $tipos = GastoItems::select('tipo')->distinct()->pluck('tipo');

        return Inertia::render('Gastos/Create', [
            'reserva' => $reserva,
            'items' => $items,
            'tipos' => $tipos,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reserva_id' => 'required|exists:reservas,id',
            'gasto_item_id' => 'required|exists:gasto_items,id',
            'fecha' => 'required|date',
        ]);

        $item = \App\Models\GastoItems::findOrFail($validated['gasto_item_id']);

        Gastos::create([
            'reserva_id'    => $validated['reserva_id'],
            'gasto_item_id' => $validated['gasto_item_id'],
            'fecha'         => $validated['fecha'],
            'monto'         => $item->precio,
        ]);

        return redirect()
            ->route('gastos.show', $validated['reserva_id'])
            ->with('success', 'Gasto agregado correctamente.');
    }
}
