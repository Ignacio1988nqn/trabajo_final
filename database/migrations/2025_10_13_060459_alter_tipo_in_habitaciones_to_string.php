<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Cambiamos la columna 'tipo' a VARCHAR(50)
        DB::statement("ALTER TABLE habitaciones MODIFY COLUMN tipo VARCHAR(50) NOT NULL");
    }

    public function down(): void
    {
        // Volvemos a ENUM por si necesitás revertir
        DB::statement("ALTER TABLE habitaciones MODIFY COLUMN tipo ENUM('simple', 'doble', 'triple', 'cuadruple') NOT NULL");
    }
};
