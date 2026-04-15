<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reservas;
use App\Models\ReservaDetalle;
use App\Models\Habitaciones;
use Carbon\Carbon;

class ReservasHistoricasSeeder extends Seeder
{
    public function run(): void
    {
        // ⚠ Ajustá estos IDs a algo que exista en tu BD
        $huespedId = 1;  // un huésped de prueba
        $usuarioId = 1;  // usuario que "crea" la reserva

        // Tomamos algunas habitaciones existentes
        $habitaciones = Habitaciones::pluck('id')->take(5);

        // Creamos reservas para los últimos 6 meses completos
        for ($i = 1; $i <= 6; $i++) {

            $mes = now()->copy()->subMonths($i)->startOfMonth();

            foreach ($habitaciones as $habitacionId) {

                // Checkin y checkout dentro de ese mes
                $checkin  = $mes->copy()->addDays(rand(0, 10));
                $checkout = $checkin->copy()->addDays(rand(2, 5));

                // === Tabla reservas ===
                $reserva = Reservas::create([
                    'huesped_id'    => $huespedId,
                    'fecha_checkin' => $checkin,
                    'fecha_checkout' => $checkout,
                    'estado'        => 'checkout', // o 'pendiente', según como cuentes ocupación
                    'usuario_id'    => $usuarioId,
                    'fecha_reserva' => $checkin->copy()->subDays(rand(1, 5)),
                    'observaciones' => 'Reserva histórica generada por seeder',
                ]);

                // === Tabla reserva_detalles ===
                ReservaDetalle::create([
                    'reserva_id'     => $reserva->id,
                    'codigo_interno' => null,
                    'descripcion'    => "Habitación $habitacionId del " .
                        $checkin->format('Y-m-d') . ' al ' .
                        $checkout->format('Y-m-d'),
                    'estado'         => 'checkout', // mismo criterio que arriba
                    'fecha_checkin'  => $checkin,
                    'fecha_checkout' => $checkout,
                ]);
            }
        }
    }
}
