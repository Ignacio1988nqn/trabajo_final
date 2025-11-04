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
        $from = $request->input('from', now()->toDateString());
        $days = (int) ($request->input('days', 14)); // cantidad de días a mostrar
        $tipo = $request->input('tipo', null);       // simple/doble/triple/cuadruple o null

        $start = Carbon::parse($from)->startOfDay();
        $end   = (clone $start)->addDays($days)->startOfDay(); // rango [start, end)

        // Habitaciones + asignaciones que SOLAPAN el rango
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

        // Array de días para encabezado
        // $daysArr = [];
        // for ($d = 0; $d < $days; $d++) {
        //     $day = (clone $start)->addDays($d);
        //     $daysArr[] = [
        //         'iso' => $day->toDateString(),
        //         'dow' => $day->isoFormat('dd'),  // LU MA MI...
        //         'dom' => $day->format('d'),      // 01..31
        //     ];
        // }
        Carbon::setLocale('es');
        $daysArr = [];
        for ($d = 0; $d < $days; $d++) {
            $day = (clone $start)->addDays($d);

            // 2 letras (Lu, Ma, Mi...):
            $dow2 = mb_strtoupper($day->isoFormat('dd'), 'UTF-8');

            // 3 letras (Lun, Mar, Mié...):
            $dow3 = rtrim($day->isoFormat('ddd'), '.'); // algunas locales traen punto final
            $dow3 = mb_convert_case($dow3, MB_CASE_TITLE, 'UTF-8');

            $daysArr[] = [
                'iso' => $day->toDateString(),
                'dow' => $dow3,                 // o $dow2 si preferís 2 letras
                'dom' => $day->format('d'),     // 01..31
            ];
        }




        // Mapear a DTO amigable para el front
        $roomDTO = $rooms->map(function ($h) use ($start, $end) {
            $bookings = $h->asignaciones->map(function ($a) use ($start, $end) {
                $ci = Carbon::parse($a->fecha_inicio)->startOfDay();
                $co = $a->fecha_fin ? Carbon::parse($a->fecha_fin)->startOfDay() : null;

                // Clampear al rango visible
                $visibleStart = $ci->max($start);
                $visibleEnd   = ($co ?? $end)->min($end); // si fin null => muestro hasta fin de rango

                // Datos de huésped
                $reserva   = $a->reserva;
                $huesped   = '—';
                if ($reserva && $reserva->huesped) {
                    $p = $reserva->huesped->personas;
                    $e = $reserva->huesped->empresas;
                    $huesped = $p ? trim(($p->apellido ?? '') . ' ' . ($p->nombre ?? '')) : ($e->razon_social ?? '—');
                }

                // Estado visual
                $estadoReserva = $reserva?->estado ?? 'pendiente'; // 'pendiente' | 'checkin' | 'checkout' | 'cancelada'
                $state = match ($estadoReserva) {
                    'checkin'  => 'ocupada',
                    'checkout' => 'checkout', // por si querés colorear distintas fases
                    'cancelada' => 'cancelada',
                    default    => 'pendiente',
                };

                return [
                    'id'         => $a->id,
                    'reserva_id' => $reserva?->id,
                    'huesped'    => $huesped,
                    'start'      => $visibleStart->toDateString(),
                    'end'        => $visibleEnd->toDateString(), // exclusivo (lo tratamos como [start, end) en el front)
                    'full_start' => $ci->toDateString(),
                    'full_end'   => $co?->toDateString(),
                    'state'      => $state,
                ];
            })->values();

            // Marcadores globales (mantenimiento/limpieza) para toda la fila
            // Si querés partirlos por fechas, tendrías que guardar histórico; aquí usamos estado_actual como "bloque".
            $blocks = in_array($h->estado_actual, ['mantenimiento', 'limpieza'])
                ? [[
                    'start' => null, // todo el rango
                    'end'   => null,
                    'state' => $h->estado_actual, // 'mantenimiento'|'limpieza'

                ]]
                : [];

            return [
                'id'      => $h->id,
                'numero'  => $h->numero,
                'tipo'    => $h->tipo,
                'estado'  => $h->estado_actual,
                'bookings' => $bookings,
                'blocks'  => $blocks,
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
