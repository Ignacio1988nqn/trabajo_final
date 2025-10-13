<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Habitaciones;

class HabitacionSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['numero' => 101, 'tipo' => 'Doble', 'precio_noche' => 45000, 'estado_actual' => 'disponible'],
            ['numero' => 102, 'tipo' => 'Suite', 'precio_noche' => 80000, 'estado_actual' => 'disponible'],
            ['numero' => 201, 'tipo' => 'Triple', 'precio_noche' => 60000, 'estado_actual' => 'disponible'],
        ];
        foreach ($data as $row) {
            Habitaciones::updateOrCreate(['numero' => $row['numero']], $row);
        }
    }
}
