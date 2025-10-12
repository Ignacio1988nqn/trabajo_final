<?php

namespace App\Http\Controllers;

use App\Models\Reservas;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class CheckoutController extends Controller
{
    public function index()
    {
        $reservas = Reservas::with(['huesped.personas', 'huesped.empresas'])
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
                };
                
                return [
                    'id' => $reserva->id,
                    'huesped' => $huespedNombre,
                    'fecha_reserva' => $reserva->fecha_reserva ? Carbon::parse($reserva->fecha_reserva)->format('d/m/Y H:i:s') : 'No asignada',
                    'fecha_checkin' => $reserva->fecha_checkin ? Carbon::parse($reserva->fecha_checkin)->format('d/m/Y H:i:s') : 'No asignada',
                    'fecha_checkout' => $reserva->fecha_checkout ? Carbon::parse($reserva->fecha_checkout)->format('d/m/Y H:i:s') : 'No asignada',
                    'estado' => $reserva->estado,
                ];
            });

        return Inertia::render('Reservas/Checkout', [
            'reservas' => $reservas,
        ]);
    }

    public function checkout(Request $request, Reservas $reserva)
    {
        if ($reserva->estado !== 'checkin') {
            return back()->withErrors(['error' => 'La reserva no está en estado check-in.']);
        }

        $reserva->update([
            'estado' => 'checkout',
            // 'fecha_checkout' => Carbon::now(), // Guarda fecha y hora completas
        ]);

        return redirect()->route('checkout.index')->with('success', 'Check-out realizado con éxito.');
    }
}
