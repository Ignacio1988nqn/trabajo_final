<?php

namespace App\Http\Controllers;

use App\Models\Reservas;
use App\Models\Gastos;
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
                    'fecha_inicio' => $reserva->fecha_checkin, // Usar fecha_checkin
                    'estado' => $reserva->estado,
                ];
            }),
        ]);
    }

    public function show(Reservas $reserva)
    {
        return Inertia::render('Gastos/GastosShow', [
            'reserva' => [
                'id' => $reserva->id,
                'estado' => $reserva->estado,
            ],
            'gastos' => $reserva->gastos()->get()->map(function ($gasto) {
                return [
                    'id' => $gasto->id,
                    'descripcion' => $gasto->descripcion,
                    'monto' => $gasto->monto,
                    'fecha' => $gasto->fecha,
                    'tipo' => $gasto->tipo,
                ];
            }),
            'tiposGasto' => ['Consumo', 'Servicio', 'Daños', 'Otros'], // Ajusta según tu lógica
        ]);
    }
    public function create($reserva_id)
    {
        $reserva = Reservas::with('huesped')->findOrFail($reserva_id);
        if (!in_array($reserva->estado, ['pendiente', 'checkin'])) {
            return redirect()->back()->with('error', 'No se pueden cargar gastos a una reserva en estado ' . $reserva->estado);
        }

        return Inertia::render('Gastos/Create', [
            'reserva' => $reserva,
            'tiposGasto' => ['habitacion', 'servicios', 'minibar', 'confiteria'],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reserva_id' => 'required|exists:reservas,id',
            'descripcion' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0',
            'fecha' => 'required|date',
            'tipo' => 'required|in:habitacion,servicios,minibar,confiteria',
        ]);

        Gastos::create($validated);

        return redirect()->route('gastos.show', $request->reserva_id);
    }
}
