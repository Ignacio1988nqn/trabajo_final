<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Habitaciones;

class HabitacionSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['numero' => 101, 'tipo' => 'Doble',     'precio_noche' => 45000, 'estado_actual' => 'disponible', 'ultima_limpieza' => now()],
            ['numero' => 104, 'tipo' => 'Simple',    'precio_noche' => 45000, 'estado_actual' => 'disponible', 'ultima_limpieza' => now()],
            ['numero' => 102, 'tipo' => 'Cuadruple', 'precio_noche' => 80000, 'estado_actual' => 'disponible', 'ultima_limpieza' => now()],
            ['numero' => 201, 'tipo' => 'Triple',    'precio_noche' => 60000, 'estado_actual' => 'disponible', 'ultima_limpieza' => now()],
        ];

        foreach ($data as $row) {
            Habitaciones::updateOrCreate(
                ['numero' => $row['numero']], // clave de búsqueda
                $row                           // valores a insertar/actualizar
            );
        }
    }
}
