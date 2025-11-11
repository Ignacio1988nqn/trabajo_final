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
        Schema::create('menu_usuario', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // nombre visible del menú
            $table->string('ruta')->nullable(); // nombre de la ruta (opcional si tiene submenus)
            $table->string('icono')->nullable();
            $table->enum('rol', ['admin', 'recepcion', 'limpieza', 'mantenimiento']);
            $table->unsignedBigInteger('padre_id')->nullable(); // para submenús
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('padre_id')->references('id')->on('menu_usuario')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_usuario');
    }
};
