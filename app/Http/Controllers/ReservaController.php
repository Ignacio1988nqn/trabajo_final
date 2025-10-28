<?php

namespace App\Http\Controllers;

use App\Models\Reservas;
use App\Models\Huespedes;
use App\Models\Habitaciones;
use App\Models\Asignaciones_habitacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReservaController extends Controller
{
    /* =========================
     |  Create (form data)
     ==========================*/
    public function create()
    {
        return Inertia::render('Reservas/Create', [
            'huespedes'    => $this->buildHuespedesOptions(),
            'habitaciones' => $this->allHabitacionesLite(),
        ]);
    }

    /* =========================
     |  Store
     ==========================*/
    public function store(Request $request)
    {
        $data = $request->validate([
            'huesped_id'      => ['required', 'exists:huespedes,id'],

            'estado'          => ['required', 'in:pendiente,checkin,checkout,cancelada'],
            'observaciones'   => ['nullable', 'string', 'max:255'],

            'asignaciones'                 => ['required', 'array', 'min:1'],
            'asignaciones.*.habitacion_id' => ['required', 'exists:habitaciones,id'],
            'asignaciones.*.fecha_inicio'  => ['required', 'date'],
            'asignaciones.*.fecha_fin'     => ['required', 'date', 'after:asignaciones.*.fecha_inicio'],
            'asignaciones.*.motivo_cambio' => ['nullable', 'string', 'max:255'],
        ], [
            'asignaciones.required' => 'Agregá al menos una habitación.',
        ]);

        // // (OPCIONAL) Mínimo 1 noche
        // foreach ($data['asignaciones'] as $idx => $a) {
        //     if (\Carbon\Carbon::parse($a['fecha_fin'])->diffInDays($a['fecha_inicio']) < 1) {
        //         return back()->withErrors([
        //             "asignaciones.$idx.fecha_fin" => 'Mínimo 1 noche.'
        //         ])->withInput();
        //     }
        // }

        // 0) Solapes internos de la MISMA habitación (entre filas del formulario)
        if ($solape = $this->haySolapesInternosMismaHabitacion($data['asignaciones'])) {
            return back()->withErrors([
                "asignaciones.{$solape['i']}.habitacion_id" => 'Esta habitación se superpone con otra fila.',
                "asignaciones.{$solape['j']}.habitacion_id" => 'Esta habitación se superpone con otra fila.',
            ])->withInput();
        }

        // 1) Fechas globales de la reserva (simplificado: siempre hay fin)
        [$fechaCheckin, $fechaCheckout] = $this->resolverFechasReserva($data);

        // 2) Validaciones cruzadas simples
        $errors = [];
        if (!$fechaCheckin) {
            $errors['fecha_checkin'] = 'Definí el check-in o el inicio de alguna asignación.';
        }
        if ($fechaCheckin && $fechaCheckout && $fechaCheckin > $fechaCheckout) {
            $errors['fecha_checkout'] = 'El check-out debe ser posterior al check-in.';
        }
        if ($errors) {
            return back()->withErrors($errors)->withInput();
        }

        // 3) Contra BD: evitar solapes con otras reservas
        foreach ($data['asignaciones'] as $idx => $a) {
            $ini = $a['fecha_inicio'];
            $fin = $a['fecha_fin']; // ahora SIEMPRE existe

            if ($this->hayConflictoEnHabitacion($a['habitacion_id'], $ini, $fin)) {
                return back()
                    ->withErrors(["asignaciones.$idx.habitacion_id" => 'La habitación no está disponible en ese rango.'])
                    ->withInput();
            }
        }

        // 4) Persistencia
        DB::transaction(function () use ($data, $fechaCheckin, $fechaCheckout) {
            $reserva = Reservas::create([
                'huesped_id'     => $data['huesped_id'],
                'fecha_checkin'  => $fechaCheckin,
                'fecha_checkout' => $fechaCheckout,
                'estado'         => $data['estado'],
                'usuario_id'     => Auth::id(),
                'fecha_reserva'  => now(),
                'observaciones'  => $data['observaciones'] ?? null,
            ]);

            foreach ($data['asignaciones'] as $a) {
                Asignaciones_habitacion::create([
                    'reserva_id'     => $reserva->id,
                    'habitacion_id'  => $a['habitacion_id'],
                    'fecha_inicio'   => $a['fecha_inicio'],
                    'fecha_fin'      => $a['fecha_fin'], // <- obligatorio
                    'motivo_cambio'  => $a['motivo_cambio'] ?? 'Asignación',
                ]);
            }
        });

        return redirect()->route('reservas.index')->with('success', 'Reserva registrada.');
    }
    /* =========================
     |  API: disponibilidad
     ==========================*/
    public function disponibles(Request $request)
    {
        $request->validate([
            'check_in'  => ['required', 'date'],
            'check_out' => ['required', 'date', 'after:check_in'],
        ]);

        $ocupadas = Asignaciones_habitacion::select('habitacion_id')
            ->where('fecha_inicio', '<', $request->check_out)
            ->where('fecha_fin',    '>', $request->check_in);

        $habitaciones = Habitaciones::whereNotIn('id', $ocupadas)
            ->orderBy('numero')
            ->get(['id', 'numero', 'tipo', 'estado_actual']);

        return response()->json($habitaciones);
    }

    /* =========================
     |  API: buscar huéspedes
     ==========================*/
    public function buscarHuespedes(Request $request)
    {
        $term = trim($request->get('q', ''));

        // Personas
        $personas = DB::table('huespedes as h')
            ->join('personas as p', 'p.huesped_id', '=', 'h.id')
            ->selectRaw("
            h.id as id,
            CONCAT(TRIM(COALESCE(p.apellido,'')), ', ', TRIM(COALESCE(p.nombre,'')),
                   CASE WHEN COALESCE(p.documento,'') <> '' THEN CONCAT(' (DNI: ', p.documento, ')') ELSE '' END
            ) as display,
            'persona' as tipo
        ");

        // Empresas
        $empresas = DB::table('huespedes as h')
            ->join('empresas as e', 'e.huesped_id', '=', 'h.id')
            ->selectRaw("
            h.id as id,
            CONCAT(TRIM(COALESCE(e.razon_social,'')),
                   CASE WHEN COALESCE(e.cuit,'') <> '' THEN CONCAT(' (CUIT: ', e.cuit, ')') ELSE '' END
            ) as display,
            'empresa' as tipo
        ");

        // Filtro por término (apellido/nombre/DNI o razón social/CUIT)
        if ($term !== '') {
            $t = "%{$term}%";
            $personas->where(function ($w) use ($t) {
                $w->where('p.apellido', 'like', $t)
                    ->orWhere('p.nombre', 'like', $t)
                    ->orWhere('p.documento', 'like', $t);
            });

            $empresas->where(function ($w) use ($t) {
                $w->where('e.razon_social', 'like', $t)
                    ->orWhere('e.cuit', 'like', $t);
            });
        }

        // Unión y orden (mostramos primero match por nombre/razón social alfabéticamente)
        $union = DB::query()
            ->fromSub($personas->unionAll($empresas), 'u')
            ->orderBy('display')
            ->limit(20)
            ->get();

        return response()->json($union);
    }


    private function haySolapesInternosMismaHabitacion(array $asignaciones): ?array
    {
        $n = count($asignaciones);
        for ($i = 0; $i < $n; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                $a = $asignaciones[$i];
                $b = $asignaciones[$j];
                if ((int)$a['habitacion_id'] !== (int)$b['habitacion_id']) continue;

                // Regla [inicio, fin): solapan si iniA < finB y iniB < finA
                if ($a['fecha_inicio'] < $b['fecha_fin'] && $b['fecha_inicio'] < $a['fecha_fin']) {
                    return ['i' => $i, 'j' => $j];
                }
            }
        }
        return null;
    }



    /* =========================
     |  Acción: terminar limpieza
     ==========================*/
    public function terminarLimpieza(Habitaciones $habitacion)
    {
        $habitacion->update(['estado_actual' => 'disponible']);
        return back()->with('success', 'Limpieza finalizada. Habitación disponible.');
    }

    /* =========================
     |  Index (listado)
     ==========================*/
    public function index()
    {
        $rows = DB::table('reservas as r')
            ->leftJoin('huespedes as hu', 'hu.id', '=', 'r.huesped_id')
            ->leftJoin('personas  as p',  'p.huesped_id',  '=', 'hu.id')
            ->leftJoin('empresas  as e',  'e.huesped_id',  '=', 'hu.id')
            ->leftJoin('asignaciones_habitacion as ah', 'ah.reserva_id', '=', 'r.id')
            ->leftJoin('habitaciones as h', 'h.id', '=', 'ah.habitacion_id')
            ->orderByDesc('r.id')
            ->orderBy('h.numero')
            ->select([
                'r.id as reserva_id',
                'r.estado',

                // fechas por asignación (si no hay asignación, usa las de la reserva)
                DB::raw('COALESCE(ah.fecha_inicio, r.fecha_checkin)  as checkin_det'),
                DB::raw('COALESCE(ah.fecha_fin,    r.fecha_checkout) as checkout_det'),

                // datos de habitación/asignación
                'ah.id      as asignacion_id',
                'h.numero   as habitacion_numero',
                'h.tipo     as habitacion_tipo',

                // nombre del huésped
                DB::raw("
                CASE 
                    WHEN hu.tipo_huesped = 'persona' 
                        THEN TRIM(CONCAT(COALESCE(p.apellido,''), ' ', COALESCE(p.nombre,'')))
                    WHEN hu.tipo_huesped = 'empresa' 
                        THEN COALESCE(e.razon_social, '')
                    ELSE '—'
                END AS huesped_nombre
            "),
            ])
            ->get();

        return Inertia::render('Reservas/Index', [
            'reservas' => $rows,   // ahora es 1 fila por asignación (o 1 por reserva si no tiene asignaciones)
        ]);
    }

    /* ==========================================================
     |                  Helpers privados
     ==========================================================*/

    /** Arma el combo de huéspedes unificando personas y empresas, ordenado por display */
    private function buildHuespedesOptions()
    {
        $personas = DB::table('huespedes')
            ->join('personas', 'personas.huesped_id', '=', 'huespedes.id')
            ->select(
                'huespedes.id',
                'huespedes.email',
                'huespedes.telefono',
                DB::raw("CONCAT(personas.apellido, ', ', personas.nombre, ' (DNI: ', personas.documento, ')') AS display")
            );

        $empresas = DB::table('huespedes')
            ->join('empresas', 'empresas.huesped_id', '=', 'huespedes.id')
            ->select(
                'huespedes.id',
                'huespedes.email',
                'huespedes.telefono',
                DB::raw("CONCAT(empresas.razon_social, ' (CUIT: ', empresas.cuit, ')') AS display")
            );

        return DB::query()
            ->fromSub($personas->unionAll($empresas), 'h')
            ->orderBy('display')
            ->get();
    }

    /** Trae habitaciones (campos mínimos) */
    private function allHabitacionesLite()
    {
        return Habitaciones::select('id', 'numero', 'tipo', 'estado_actual')
            ->orderBy('numero')
            ->get();
    }

    /**
     * Resuelve fecha_checkin y fecha_checkout de la reserva:
     *  - Todas tienen fin => checkout = max(fin) o el que venga del form.
     */
    private function resolverFechasReserva(array $data): array
    {
        $minInicio = collect($data['asignaciones'])->pluck('fecha_inicio')->filter()->min();
        $maxFin    = collect($data['asignaciones'])->pluck('fecha_fin')->filter()->max();

        return [
            $data['fecha_checkin']  ?? $minInicio ?? null,
            $data['fecha_checkout'] ?? $maxFin    ?? null,
        ];
    }

    /**
     * Chequea solape de una habitación contra [ini, fin].
     * Regla: conflictos si existe [inicio_ex, fin_ex) tal que ini < fin_ex y fin > inicio_ex
     */
    private function hayConflictoEnHabitacion(int $habitacionId, string $ini, string $fin): bool
    {
        // Conflicto si existe un rango [inicio_ex, fin_ex) tal que ini < fin_ex y fin > inicio_ex
        return Asignaciones_habitacion::where('habitacion_id', $habitacionId)
            ->where('fecha_inicio', '<', $fin)
            ->where('fecha_fin',    '>', $ini)
            ->exists();
    }
}
