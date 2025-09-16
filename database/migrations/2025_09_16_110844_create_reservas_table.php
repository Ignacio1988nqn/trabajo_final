<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('huesped_id')->constrained('huespedes')->onDelete('cascade');
            $table->date('fecha_checkin')->nullable();
            $table->date('fecha_checkout')->nullable();
            $table->enum('estado', ['pendiente', 'checkin', 'checkout', 'cancelada']);
            $table->foreignId('usuario_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('fecha_reserva');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
