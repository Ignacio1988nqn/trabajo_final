// database/migrations/2025_11_20_000002_update_asignaciones_habitacion_add_reserva_detalle_id.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asignaciones_habitacion', function (Blueprint $table) {
            // 1. Agregamos la nueva columna (nullable al principio)
            $table->foreignId('reserva_detalle_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('reserva_detalles')
                  ->nullOnDelete(); // si se borra el folio, se borra la asignación

            // 2. Eliminamos la relación directa con reserva (ya no la necesitamos aquí)
            // $table->dropForeign(['reserva_id']);
            // $table->dropColumn('reserva_id');

            // Índices importantes
            // $table->index(['reserva_detalle_id', 'fecha_inicio']);
            // $table->index('fecha_fin');
        });
    }

    public function down(): void
    {
        Schema::table('asignaciones_habitacion', function (Blueprint $table) {
            // Volvemos atrás (solo para rollback)
            $table->foreignId('reserva_id')
                  ->after('id')
                  ->constrained('reservas')
                  ->cascadeOnDelete();

            $table->dropForeign(['reserva_detalle_id']);
            $table->dropConstrainedForeignId('reserva_detalle_id');
        });
    }
};
// use Illuminate\Database\Migrations\Migration;
// use Illuminate\Database\Schema\Blueprint;
// use Illuminate\Support\Facades\Schema;
// use Illuminate\Support\Facades\DB;

// return new class extends Migration
// {
//     /**
//      * Run the migrations.
//      */
//     public function up(): void
//     {
//         Schema::table('asignaciones_habitacion', function (Blueprint $table) {
//             // 1. Agregar la nueva columna reserva_detalle_id
//             $table->foreignId('reserva_detalle_id')
//                   ->after('id')
//                   ->constrained('reserva_detalles')
//                   ->cascadeOnDelete();

//             // 2. Eliminar la columna vieja reserva_id (si existe)
//             if (Schema::hasColumn('asignaciones_habitacion', 'reserva_id')) {
//                 $table->dropForeign(['reserva_id']);
//                 $table->dropColumn('reserva_id');
//             }

//             // 3. Índices (solo si no existen ya → evita el error 1061)
//             $this->createIndexIfNotExists($table, ['reserva_detalle_id', 'fecha_inicio'], 'asignaciones_habitacion_reserva_detalle_id_fecha_inicio_index');
//             $this->createIndexIfNotExists($table, 'fecha_fin', 'asignaciones_habitacion_fecha_fin_index');
//         });
//     }

//     /**
//      * Reverse the migrations.
//      */
//     public function down(): void
//     {
//         Schema::table('asignaciones_habitacion', function (Blueprint $table) {
//             // Quitamos la nueva columna
//             if (Schema::hasColumn('asignaciones_habitacion', 'reserva_detalle_id')) {
//                 $table->dropForeign(['reserva_detalle_id']);
//                 $table->dropColumn('reserva_detalle_id');
//             }

//             // Restauramos la columna vieja solo si no existe
//             if (!Schema::hasColumn('asignaciones_habitacion', 'reserva_id')) {
//                 $table->foreignId('reserva_id')
//                       ->after('id')
//                       ->constrained('reservas')
//                       ->cascadeOnDelete();
//             }

//             // Borramos índices si existen (opcional, pero limpio)
//             $table->dropIndexIfExists('asignaciones_habitacion_reserva_detalle_id_fecha_inicio_index');
//             $table->dropIndexIfExists('asignaciones_habitacion_fecha_fin_index');
//         });
//     }

//     /**
//      * Helper para crear índice solo si no existe
//      */
//     private function createIndexIfNotExists(Blueprint $table, $columns, $indexName)
//     {
//         $exists = collect(DB::select("SHOW INDEX FROM asignaciones_habitacion WHERE Key_name = ?", [$indexName]))->isNotEmpty();

//         if (!$exists) {
//             if (is_array($columns)) {
//                 $table->index($columns, $indexName);
//             } else {
//                 $table->index($columns, $indexName);
//             }
//         }
//     }
// };