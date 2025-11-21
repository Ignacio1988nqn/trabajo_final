<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Reservas;
use App\Models\Asignaciones_habitacion;
use App\Models\Habitaciones;

class ReservaDetalle extends Model
{
    use HasFactory;

    protected $table = 'reserva_detalles';

    protected $fillable = [
        'reserva_id',
        'nombre',
        'codigo_interno',
        'descripcion',
        'estado',
        'fecha_checkin',
        'fecha_checkout'
    ];

    protected $casts = [
        'estado' => 'string',
    ];

    // Una línea/folio pertenece a una reserva
    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reservas::class);
    }

    // Un folio tiene muchas asignaciones de habitación
    public function asignacionesHabitacion(): HasMany
    {
        return $this->hasMany(Asignaciones_habitacion::class, 'reserva_detalle_id');
    }

    // Un folio tiene muchas habitaciones (a través de las asignaciones)
    public function habitaciones()
    {
        return $this->belongsToMany(Habitaciones::class, 'asignaciones_habitacion', 'reserva_detalle_id', 'habitacion_id')
            ->withPivot('fecha_inicio', 'fecha_fin', 'motivo_cambio')
            ->withTimestamps();
    }

    // Scope para folios activos
    public function scopeActivos($query)
    {
        return $query->whereIn('estado', ['activo', 'checkin']);
    }

    // Saber si este folio ya hizo checkout completo
    public function estaCheckout(): bool
    {
        return $this->estado === 'checkout' || $this->estado === 'cancelado';
    }

    // Habitación actual de este folio (si hay solo una activa)
    public function habitacionActual()
    {
        return $this->asignacionesHabitacion()
            ->where(function ($q) {
                $q->whereNull('fecha_fin')
                    ->orWhere('fecha_fin', '>=', today());
            })
            ->where('fecha_inicio', '<=', today())
            ->latest('fecha_inicio')
            ->first()?->habitacion;
    }

    public function gastos(): HasMany
    {
        return $this->hasMany(Gastos::class, 'reserva_detalle_id');
    }
}
