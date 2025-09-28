<?php

namespace App\Http\Controllers;

use App\Models\Reservas;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class CheckinController extends Controller
{
    public function index()
    {
        // Obtener reservas pendientes con datos del huésped
        $reservas = Reservas::with('huesped')
            ->where('estado', 'pendiente')
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
                    'id' => $reserva->id,
                    'huesped' => $huespedNombre,
                    'fecha_reserva' => $reserva->fecha_reserva->format('d/m/Y H:i'),
                    'fecha_checkin' => $reserva->fecha_checkin ? $reserva->fecha_checkin->format('d/m/Y') : 'No asignada',
                    'fecha_checkout' => $reserva->fecha_checkout ? $reserva->fecha_checkout->format('d/m/Y') : 'No asignada',
                    'estado' => $reserva->estado,
                ];
            });
        // dd($reservas);
        return Inertia::render('Reservas/Checkin', [
            'reservas' => $reservas,
        ]);
    }

    public function checkin(Request $request, Reservas $reserva)
    {
        // Validar que la reserva esté en estado pendiente
        if ($reserva->estado !== 'pendiente') {
            return back()->withErrors(['error' => 'La reserva no está en estado pendiente.']);
        }

        // Actualizar estado a checkin y asignar fecha actual
        $reserva->update([
            'estado' => 'checkin',
            'fecha_checkin' => Carbon::now(),
        ]);

        return redirect()->route('checkin.index')->with('success', 'Check-in realizado con éxito.');
    }
}
