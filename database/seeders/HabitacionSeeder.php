<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Habitaciones;

class HabitacionSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['numero' => 101, 'tipo' => 'Simple',    'precio_noche' => 45000, 'estado_actual' => 'disponible', 'ultima_limpieza' => now()],
            ['numero' => 102, 'tipo' => 'Doble',     'precio_noche' => 60000, 'estado_actual' => 'disponible', 'ultima_limpieza' => now()],
            ['numero' => 103, 'tipo' => 'Triple',    'precio_noche' => 75000, 'estado_actual' => 'disponible', 'ultima_limpieza' => now()],
            ['numero' => 104, 'tipo' => 'Simple',    'precio_noche' => 45000, 'estado_actual' => 'disponible', 'ultima_limpieza' => now()],
            ['numero' => 105, 'tipo' => 'Doble',     'precio_noche' => 60000, 'estado_actual' => 'disponible', 'ultima_limpieza' => now()],
            ['numero' => 106, 'tipo' => 'Cuádruple', 'precio_noche' => 90000, 'estado_actual' => 'disponible', 'ultima_limpieza' => now()],
            ['numero' => 201, 'tipo' => 'Simple',    'precio_noche' => 50000, 'estado_actual' => 'disponible', 'ultima_limpieza' => now()],
            ['numero' => 202, 'tipo' => 'Doble',     'precio_noche' => 65000, 'estado_actual' => 'disponible', 'ultima_limpieza' => now()],
            ['numero' => 203, 'tipo' => 'Triple',    'precio_noche' => 80000, 'estado_actual' => 'disponible', 'ultima_limpieza' => now()],
            ['numero' => 204, 'tipo' => 'Suite',     'precio_noche' => 120000, 'estado_actual' => 'disponible', 'ultima_limpieza' => now()],
        ];

        foreach ($data as $row) {
            Habitaciones::updateOrCreate(
                ['numero' => $row['numero']], // clave única
                $row
            );
        }
    }
}
