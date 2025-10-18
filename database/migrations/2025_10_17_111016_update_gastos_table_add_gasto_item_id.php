<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gastos', function (Blueprint $table) {
            // Nueva relación con gasto_items
            $table->foreignId('gasto_item_id')->after('reserva_id')->constrained('gasto_items')->onDelete('cascade');

            // Ya no necesitamos estos campos
            $table->dropColumn(['descripcion', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::table('gastos', function (Blueprint $table) {
            $table->dropForeign(['gasto_item_id']);
            $table->dropColumn('gasto_item_id');

            $table->string('descripcion', 255);
            $table->enum('tipo', ['habitacion', 'servicios', 'minibar', 'confiteria']);
        });
    }
};
