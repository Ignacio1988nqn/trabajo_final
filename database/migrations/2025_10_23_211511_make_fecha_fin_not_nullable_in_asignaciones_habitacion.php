<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Primero, por si hubiera nulos, los rellena
        DB::statement("
            UPDATE asignaciones_habitacion
            SET fecha_fin = DATE_ADD(fecha_inicio, INTERVAL 1 DAY)
            WHERE fecha_fin IS NULL
        ");

        Schema::table('asignaciones_habitacion', function (Blueprint $table) {
            $table->date('fecha_fin')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('asignaciones_habitacion', function (Blueprint $table) {
            $table->date('fecha_fin')->nullable()->change();
        });
    }
};
