<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Habitaciones;
use Illuminate\Support\Facades\DB;
use App\Models\Asignaciones_habitacion;
use Carbon\Carbon;

class EstadisticasController extends Controller
{
    public function index(Request $request)
    {
        $estados = Habitaciones::select('estado_actual', DB::raw('count(*) as total'))
            ->groupBy('estado_actual')
            ->pluck('total', 'estado_actual');

        // === 2️⃣ Ocupación mensual (hasta el día actual) ===
        $hoy = Carbon::today();
        $inicioMes = $hoy->copy()->startOfMonth();
        $diaActual = $hoy->day;
        $habitacionesTotales = Habitaciones::count();

        $diasOcupados = Asignaciones_habitacion::where(function ($q) use ($inicioMes, $hoy) {
            $q->whereBetween('fecha_inicio', [$inicioMes, $hoy])
                ->orWhereBetween('fecha_fin', [$inicioMes, $hoy])
                ->orWhere(function ($sub) use ($inicioMes, $hoy) {
                    $sub->where('fecha_inicio', '<=', $inicioMes)
                        ->where(function ($s) use ($hoy) {
                            $s->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', $hoy);
                        });
                });
        })
            ->get()
            ->sum(function ($a) use ($inicioMes, $hoy) {
                $inicio = Carbon::parse($a->fecha_inicio)->lt($inicioMes) ? $inicioMes : Carbon::parse($a->fecha_inicio);
                $fin = $a->fecha_fin ? Carbon::parse($a->fecha_fin) : $hoy;
                if ($fin->gt($hoy)) $fin = $hoy;
                return $inicio->diffInDaysFiltered(fn($d) => true, $fin) + 1;
            });

        $diasDisponibles = $habitacionesTotales * $diaActual;
        $porcentajeOcupacionActual = $diasDisponibles > 0 ? round(($diasOcupados / $diasDisponibles) * 100, 2) : 0;

        // === 3️⃣ Histórico últimos 12 meses (sin incluir mes actual) ===
        $historico = [];
        $labels = [];

        for ($i = 12; $i >= 1; $i--) { // del mes anterior hacia atrás 12 meses
            $inicio = Carbon::now()->startOfMonth()->subMonths($i);
            $fin = $inicio->copy()->endOfMonth();
            $diasMes = $inicio->daysInMonth;

            $diasOcupadosMes = Asignaciones_habitacion::where(function ($q) use ($inicio, $fin) {
                $q->whereBetween('fecha_inicio', [$inicio, $fin])
                    ->orWhereBetween('fecha_fin', [$inicio, $fin])
                    ->orWhere(function ($sub) use ($inicio, $fin) {
                        $sub->where('fecha_inicio', '<=', $inicio)
                            ->where(function ($s) use ($fin) {
                                $s->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', $fin);
                            });
                    });
            })
                ->get()
                ->sum(function ($a) use ($inicio, $fin) {
                    $ini = Carbon::parse($a->fecha_inicio)->lt($inicio) ? $inicio : Carbon::parse($a->fecha_inicio);
                    $f = $a->fecha_fin ? Carbon::parse($a->fecha_fin) : $fin;
                    if ($f->gt($fin)) $f = $fin;
                    return $ini->diffInDaysFiltered(fn($d) => true, $f) + 1;
                });

            $totalPosible = Habitaciones::count() * $diasMes;
            $ocupacion = $totalPosible > 0 ? round(($diasOcupadosMes / $totalPosible) * 100, 2) : 0;

            $labels[] = $inicio->format('M Y');
            $historico[] = $ocupacion;
        }

        return Inertia::render('Estadisticas/Index', [
            'estados' => $estados,
            'ocupacionActual' => $porcentajeOcupacionActual,
            'diaActual' => $diaActual,
            'historico' => [
                'data' => $historico,
                'labels' => $labels,
            ],
        ]);
    }
}
