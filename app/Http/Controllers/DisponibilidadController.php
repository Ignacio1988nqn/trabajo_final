<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Habitaciones;
use Carbon\Carbon;
use Inertia\Inertia;

class DisponibilidadController extends Controller
{
    public function index(Request $request)
    {
        // Defaults: hoy / +7 días / 'todos'
        $fechaInicio = $request->input('fecha_inicio') ?? Carbon::today()->toDateString();
        $dias        = (int)($request->input('dias') ?? 7);
        $fechaFin    = $request->input('fecha_fin') ?? Carbon::parse($fechaInicio)->copy()->addDays($dias)->toDateString();
        $tipo        = $request->input('tipo') ?? 'todos';

        // Base query
        $habitaciones = Habitaciones::query()
            // si elegís un tipo, filtramos; si es 'todos' no filtramos por tipo
            ->when($tipo !== 'todos', fn($q) => $q->where('tipo', $tipo))
            // (opcional) ocultar las que están en mantenimiento
            ->where('estado_actual', '!=', 'mantenimiento')
            // libres en el rango [inicio, fin)
            ->whereDoesntHave('asignaciones', function ($q) use ($fechaInicio, $fechaFin) {
                $q->where('fecha_inicio', '<', $fechaFin)
                    ->where(function ($q2) use ($fechaInicio) {
                        $q2->where('fecha_fin', '>', $fechaInicio)
                            ->orWhereNull('fecha_fin');
                    });
            })
            ->orderBy('numero')
            ->get();

        return Inertia::render('Reservas/Disponibilidad', [
            'habitaciones'  => $habitaciones,
            'tipo'          => $tipo,               // 'todos' por default
            'fecha_inicio'  => $fechaInicio,        // hoy por default
            'fecha_fin'     => $fechaFin,           // hoy + 7 por default
            'dias'          => $dias,               // útil si querés mostrar el selector
        ]);
    }


    public function calendario(Request $request)
    {
        // 1) Normalizar lo que viene del request
        $rawFrom = $request->input('from');      // puede venir "" o null
        $rawDays = $request->input('days');      // puede venir "" o null
        $rawTipo = $request->input('tipo');      // "" = todos

        // Si no viene o viene vacío => hoy
        $from = $rawFrom ?: now()->toDateString();

        // Si no viene o viene vacío => 7 días
        $days = (int) ($rawDays ?: 7);
        if ($days <= 0) {
            $days = 7;
        }

        // "" lo tratamos como null (Todos)
        $tipo = $rawTipo !== '' ? $rawTipo : null;

        // ---------------------------------------------------
        // A PARTIR DE ACÁ VA IGUAL QUE LO TENÍAS
        // ---------------------------------------------------

        $start = Carbon::parse($from)->startOfDay();
        $end   = (clone $start)->addDays($days)->startOfDay(); // rango [start, end)

        $roomsQuery = Habitaciones::query()
            ->when($tipo, fn($q) => $q->where('tipo', $tipo))
            ->with(['asignaciones' => function ($q) use ($start, $end) {
                $q->where('fecha_inicio', '<', $end->toDateString())
                    ->where(function ($w) use ($start) {
                        $w->whereNull('fecha_fin')
                            ->orWhere('fecha_fin', '>', $start->toDateString());
                    })
                    ->with(['reserva.huesped.personas', 'reserva.huesped.empresas']);
            }])
            ->orderBy('tipo')
            ->orderBy('numero');

        $rooms = $roomsQuery->get();

        Carbon::setLocale('es');
        $daysArr = [];
        for ($d = 0; $d < $days; $d++) {
            $day = (clone $start)->addDays($d);

            $dow3 = rtrim($day->isoFormat('ddd'), '.');
            $dow3 = mb_convert_case($dow3, MB_CASE_TITLE, 'UTF-8');

            $daysArr[] = [
                'iso' => $day->toDateString(),
                'dow' => $dow3,
                'dom' => $day->format('d'),
            ];
        }

        $roomDTO = $rooms->map(function ($h) use ($start, $end) {
            $bookings = $h->asignaciones->map(function ($a) use ($start, $end) {
                $ci = Carbon::parse($a->fecha_inicio)->startOfDay();
                $co = $a->fecha_fin ? Carbon::parse($a->fecha_fin)->startOfDay() : null;

                $visibleStart = $ci->max($start);
                $visibleEnd   = ($co ?? $end)->min($end);

                // $reserva = $a->reserva;
                // $huesped = '—';
                // if ($reserva && $reserva->huesped) {
                //     $p = $reserva->huesped->personas;
                //     $e = $reserva->huesped->empresas;
                //     $huesped = $p
                //         ? trim(($p->apellido ?? '') . ' ' . ($p->nombre ?? ''))
                //         : ($e->razon_social ?? '—');
                // }
                $reserva = $a->reserva;
                $huesped = '—';

                if ($reserva && $reserva->huesped) {
                    $hModel = $reserva->huesped;

                    $p = $hModel->personas;
                    $e = $hModel->empresas;

                    $parts = [];

                    // Si hay persona, la agregamos
                    if ($p) {
                        $parts[] = trim(($p->apellido ?? '') . ' ' . ($p->nombre ?? ''));
                    }

                    // Si hay empresa, también la agregamos
                    if ($e) {
                        $parts[] = $e->razon_social ?? '';
                    }

                    // Unimos lo que haya con " / "
                    if (count($parts)) {
                        $huesped = implode(' / ', array_filter($parts));
                    } else {
                        $huesped = 'Sin datos';
                    }
                }

                $estadoReserva = $reserva?->estado ?? 'pendiente';
                $state = match ($estadoReserva) {
                    'checkin'   => 'ocupada',
                    'checkout'  => 'checkout',
                    'cancelada' => 'cancelada',
                    default     => 'pendiente',
                };


                return [
                    'id'         => $a->id,
                    'reserva_id' => $reserva?->id,
                    'huesped'    => $huesped,
                    'start'      => $visibleStart->toDateString(),
                    'end'        => $visibleEnd->toDateString(),
                    'full_start' => $ci->toDateString(),
                    'full_end'   => $co?->toDateString(),
                    'state'      => $state,
                ];
            })->values();

            $blocks = in_array($h->estado_actual, ['mantenimiento', 'limpieza'])
                ? [[
                    'start' => null,
                    'end'   => null,
                    'state' => $h->estado_actual,
                ]]
                : [];

            return [
                'id'       => $h->id,
                'numero'   => $h->numero,
                'tipo'     => $h->tipo,
                'estado'   => $h->estado_actual,
                'bookings' => $bookings,
                'blocks'   => $blocks,
            ];
        })->values();

        return Inertia::render('Reservas/Disponibilidad', [
            'from'    => $start->toDateString(),
            'days'    => $days,
            'daysArr' => $daysArr,
            'tipo'    => $tipo,
            'rooms'   => $roomDTO,
        ]);
    }
}
