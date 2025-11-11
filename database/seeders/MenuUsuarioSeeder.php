<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MenuUsuario;

class MenuUsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
          // Limpia la tabla antes de insertar
        MenuUsuario::truncate();

        /*
        |--------------------------------------------------------------------------
        | ADMIN
        |--------------------------------------------------------------------------
        | Tiene acceso total a todos los módulos
        */

        // Dashboard
        MenuUsuario::create([
            'nombre' => 'Inicio',
            'ruta' => 'dashboard',
            'rol' => 'admin',
            'orden' => 1,
        ]);

        // Huesped (submenu)
        $menuHuespedAdmin = MenuUsuario::create([
            'nombre' => 'Huésped',
            'rol' => 'admin',
            'orden' => 2,
        ]);

        MenuUsuario::create([
            'nombre' => 'Huésped',
            'ruta' => 'huesped.index',
            'rol' => 'admin',
            'padre_id' => $menuHuespedAdmin->id,
            'orden' => 1,
        ]);

        // Reservas (submenu)
        $menuReservasAdmin = MenuUsuario::create([
            'nombre' => 'Reservas',
            'rol' => 'admin',
            'orden' => 3,
        ]);

        MenuUsuario::insert([
            ['nombre' => 'Reservas', 'ruta' => 'reservas.index', 'rol' => 'admin', 'padre_id' => $menuReservasAdmin->id, 'orden' => 1],
            ['nombre' => 'Check-In', 'ruta' => 'checkin.index', 'rol' => 'admin', 'padre_id' => $menuReservasAdmin->id, 'orden' => 2],
            ['nombre' => 'Check-Out', 'ruta' => 'checkout.index', 'rol' => 'admin', 'padre_id' => $menuReservasAdmin->id, 'orden' => 3],
            ['nombre' => 'Disponibilidad', 'ruta' => 'disponibilidad.index', 'rol' => 'admin', 'padre_id' => $menuReservasAdmin->id, 'orden' => 4],
        ]);

        // Gastos (submenu)
        $menuGastosAdmin = MenuUsuario::create([
            'nombre' => 'Gastos',
            'rol' => 'admin',
            'orden' => 4,
        ]);

        MenuUsuario::insert([
            ['nombre' => 'Gastos', 'ruta' => 'gastos.index', 'rol' => 'admin', 'padre_id' => $menuGastosAdmin->id, 'orden' => 1],
            ['nombre' => 'Ítems de Gasto', 'ruta' => 'gasto-items.index', 'rol' => 'admin', 'padre_id' => $menuGastosAdmin->id, 'orden' => 2],
        ]);

        // Habitaciones
        MenuUsuario::create([
            'nombre' => 'Habitaciones',
            'ruta' => 'habitaciones.index',
            'rol' => 'admin',
            'orden' => 5,
        ]);

        // Limpieza
        MenuUsuario::create([
            'nombre' => 'Limpieza',
            'ruta' => 'limpieza.index',
            'rol' => 'admin',
            'orden' => 6,
        ]);

        // Estadísticas
        MenuUsuario::create([
            'nombre' => 'Estadísticas',
            'ruta' => 'estadisticas.index',
            'rol' => 'admin',
            'orden' => 7,
        ]);

        /*
        |--------------------------------------------------------------------------
        | RECEPCIÓN
        |--------------------------------------------------------------------------
        | Todo excepto limpieza
        */

        // Dashboard
        MenuUsuario::create([
            'nombre' => 'Inicio',
            'ruta' => 'dashboard',
            'rol' => 'recepcion',
            'orden' => 1,
        ]);

        // Huesped (submenu)
        $menuHuespedRecep = MenuUsuario::create([
            'nombre' => 'Huésped',
            'rol' => 'recepcion',
            'orden' => 2,
        ]);

        MenuUsuario::create([
            'nombre' => 'Huésped',
            'ruta' => 'huesped.index',
            'rol' => 'recepcion',
            'padre_id' => $menuHuespedRecep->id,
            'orden' => 1,
        ]);

        // Reservas (submenu)
        $menuReservasRecep = MenuUsuario::create([
            'nombre' => 'Reservas',
            'rol' => 'recepcion',
            'orden' => 3,
        ]);

        MenuUsuario::insert([
            ['nombre' => 'Reservas', 'ruta' => 'reservas.index', 'rol' => 'recepcion', 'padre_id' => $menuReservasRecep->id, 'orden' => 1],
            ['nombre' => 'Check-In', 'ruta' => 'checkin.index', 'rol' => 'recepcion', 'padre_id' => $menuReservasRecep->id, 'orden' => 2],
            ['nombre' => 'Check-Out', 'ruta' => 'checkout.index', 'rol' => 'recepcion', 'padre_id' => $menuReservasRecep->id, 'orden' => 3],
            ['nombre' => 'Disponibilidad', 'ruta' => 'disponibilidad.index', 'rol' => 'recepcion', 'padre_id' => $menuReservasRecep->id, 'orden' => 4],
        ]);

        // Gastos (submenu)
        $menuGastosRecep = MenuUsuario::create([
            'nombre' => 'Gastos',
            'rol' => 'recepcion',
            'orden' => 4,
        ]);

        MenuUsuario::insert([
            ['nombre' => 'Gastos', 'ruta' => 'gastos.index', 'rol' => 'recepcion', 'padre_id' => $menuGastosRecep->id, 'orden' => 1],
            ['nombre' => 'Ítems de Gasto', 'ruta' => 'gasto-items.index', 'rol' => 'recepcion', 'padre_id' => $menuGastosRecep->id, 'orden' => 2],
        ]);

         // Habitaciones
        MenuUsuario::create([
            'nombre' => 'Habitaciones',
            'ruta' => 'habitaciones.index',
            'rol' => 'recepcion',
            'orden' => 5,
        ]);

        // Estadísticas
        MenuUsuario::create([
            'nombre' => 'Estadísticas',
            'ruta' => 'estadisticas.index',
            'rol' => 'recepcion',
            'orden' => 6,
        ]);

        /*
        |--------------------------------------------------------------------------
        | LIMPIEZA
        |--------------------------------------------------------------------------
        | Solo ve el módulo de limpieza
        */

        MenuUsuario::create([
            'nombre' => 'Limpieza',
            'ruta' => 'limpieza.index',
            'rol' => 'limpieza',
            'orden' => 1,
        ]);

        /*
        |--------------------------------------------------------------------------
        | MANTENIMIENTO (básico)
        |--------------------------------------------------------------------------
        */

        MenuUsuario::create([
            'nombre' => 'Mantenimiento',
            'ruta' => 'mantenimiento.index',
            'rol' => 'mantenimiento',
            'orden' => 1,
        ]);
    }
}
