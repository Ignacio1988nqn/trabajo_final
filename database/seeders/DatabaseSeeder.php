<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Usuario de prueba (idempotente)
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // Registrar seeders
        $this->call([
            HabitacionSeeder::class,
            // ...agregá aquí otros seeders si tenés (GastoItemsSeeder, etc.)
        ]);
    }
}
