<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gastos', function (Blueprint $table) {
            // 1. Agregamos la nueva columna (nullable al principio)
            $table->foreignId('reserva_detalle_id')
                  ->nullable()
                  ->after('reserva_id')  // queda al lado para que sea fácil de ver
                  ->constrained('reserva_detalles')
                  ->onDelete('cascade');

            // 2. Índice para búsquedas rápidas
            // $table->index('reserva_detalle_id');
        });

    }

    public function down(): void
    {
        Schema::table('gastos', function (Blueprint $table) {
            // Eliminar foreign e índice
            $table->dropForeign(['reserva_detalle_id']);
            $table->dropIndex(['reserva_detalle_id']);
            $table->dropColumn('reserva_detalle_id');
        });
    }
};