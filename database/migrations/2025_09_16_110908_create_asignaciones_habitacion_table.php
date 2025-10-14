<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // NOMBRE CORRECTO DE LA TABLA
        Schema::create('asignaciones_habitacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reserva_id')->constrained('reservas')->cascadeOnDelete();
            $table->foreignId('habitacion_id')->constrained('habitaciones')->cascadeOnDelete();
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->string('motivo_cambio', 255)->nullable();
            // $table->timestamps(); // si querés auditoría
        });
    }

    public function down(): void
    {
        // NOMBRE CORRECTO EN EL DROP
        Schema::dropIfExists('asignaciones_habitacion');
    }
};
