<?php

namespace App\Http\Controllers;

use App\Models\Reservas;
use App\Models\Gastos;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GastosControllerold extends Controller
{
    public function index()
    {
        $reservas = Reservas::with('huesped')
            ->whereIn('estado', ['pendiente', 'checkin'])
            ->get();

        return Inertia::render('Gastos/Index', [
            'reservas' => $reservas,
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
        $request->validate([
            'reserva_id' => 'required|exists:reservas,id',
            'descripcion' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0',
            'fecha' => 'required|date',
            'tipo' => 'required|in:habitacion,servicios,minibar,confiteria',
        ]);

        Gastos::create($request->all());

        return redirect()->route('gastos.index')
            ->with('success', 'Gasto registrado correctamente');
        return redirect()->route('reservas.show', $request->reserva_id)
            ->with('success', 'Gasto registrado correctamente');
    }
}
