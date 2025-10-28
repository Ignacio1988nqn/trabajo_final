<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // database/migrations/xxxx_add_observaciones_to_reservas_table.php
    public function up()
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->string('observaciones')->nullable()->after('fecha_reserva');
        });
    }
    public function down()
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn('observaciones');
        });
    }
};
