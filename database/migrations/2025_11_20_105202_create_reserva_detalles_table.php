// database/migrations/2025_11_20_000001_create_reserva_detalles_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reserva_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reserva_id')
                  ->constrained('reservas')
                  ->cascadeOnDelete(); // si borras la reserva, borra todos sus folios

            // Nombre visible para el usuario (ej: "Ingenieros", "Operarios Pozo", "Grupo A")
            // $table->string('nombre', 100);

            // Código interno que usa la empresa (centro de costo, PO, orden de trabajo, etc.)
            $table->string('codigo_interno', 50)->nullable()->unique();

            // Descripción opcional
            $table->text('descripcion')->nullable();

            // Estado del folio (para poder cerrar uno sin cerrar toda la reserva)
            $table->enum('estado', ['pendiente', 'checkin', 'checkout', 'cancelado'])
                  ->default('pendiente');
            $table->date('fecha_checkin')->nullable();
            $table->date('fecha_checkout')->nullable();
            $table->timestamps();

            // Índice para búsquedas rápidas
            $table->index(['reserva_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reserva_detalles');
    }
};