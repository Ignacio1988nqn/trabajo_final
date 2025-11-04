<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HuespedController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\GastosController;
use App\Http\Controllers\HabitacionController;
use App\Http\Controllers\DisponibilidadController;
use App\Http\Controllers\GastoItemsController;
use App\Http\Controllers\LimpiezaController;


Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return Inertia::render('Auth/Login'); // Ajustado para apuntar a resources/js/Pages/Auth/Login.vue
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/counter', function () {
    return view('counter');
});
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('huesped', HuespedController::class);
});
Route::get('/reservas/buscarhuespedes', [ReservaController::class, 'buscarHuespedes'])
    ->name('reservas.buscarHuespedes');

Route::middleware(['auth', 'verified'])->group(function () {
    // Listado
    Route::get(
        '/reservas',
        fn() =>
        Inertia::render('Reservas/Index')
    )->name('reservas.index');

    // Crear
    Route::get('/reservas/crear', [ReservaController::class, 'create'])->name('reservas.create');

    //endpoint para ver información   
    Route::get('/reservas', [ReservaController::class, 'index'])->name('reservas.index');
    Route::post('/reservas',      [ReservaController::class, 'store'])->name('reservas.store');

    // (opcionales)
    Route::get('/reservas/disponibles', [ReservaController::class, 'disponibles'])->name('reservas.disponibles');
    Route::get('/huespedes/buscar',     [ReservaController::class, 'buscarHuespedes'])->name('huespedes.buscar');

    //A VER SI ES ESTO
    Route::resource('habitaciones', HabitacionController::class)->only(['index', 'create', 'store']);
    Route::resource('reservas', ReservaController::class)->only(['index', 'create', 'store']);
});

Route::get('/checkin', [CheckinController::class, 'index'])->middleware(['auth', 'verified'])->name('checkin.index');
Route::post('/checkin/{reserva}', [CheckinController::class, 'checkin'])->middleware(['auth', 'verified'])->name('checkin.store');
Route::get('/checkin', [CheckinController::class, 'index'])->name('checkin.index');

Route::post('/checkin/{reserva}', [CheckinController::class, 'checkin'])->name('checkin.do');
Route::get('/checkout', [CheckoutController::class, 'index'])->middleware(['auth', 'verified'])->name('checkout.index');
Route::post('/checkout/{reserva}', [CheckoutController::class, 'checkout'])->middleware(['auth', 'verified'])->name('checkout.store');

Route::get('/reservas/{reserva}/gastos/create', [GastosController::class, 'create'])->middleware(['auth', 'verified'])->name('gastos.create');
Route::get('/gastos', [GastosController::class, 'index'])->middleware(['auth', 'verified'])->name('gastos.index');
Route::get('/gastos/{reserva}', [GastosController::class, 'show'])->middleware(['auth', 'verified'])->name('gastos.show');
Route::post('/gastos', [GastosController::class, 'store'])->middleware(['auth', 'verified'])->name('gastos.store');

Route::put('/habitaciones/{id}/estado', [HabitacionController::class, 'actualizarEstado'])->name('habitaciones.actualizarEstado');
Route::get('/habitaciones', [HabitacionController::class, 'index'])->middleware(['auth', 'verified'])->name('habitaciones.index');

Route::get('/disponibilidad', [DisponibilidadController::class, 'index'])->middleware(['auth', 'verified'])->name('disponibilidad.index');

// Route::get('/habitaciones', [HabitacionController::class, 'index'])->name('habitaciones.index');
Route::get('/habitaciones/create', [HabitacionController::class, 'create'])->middleware(['auth', 'verified'])->name('habitaciones.create');
Route::post('/habitaciones', [HabitacionController::class, 'store'])->middleware(['auth', 'verified'])->name('habitaciones.store');
Route::post('/habitaciones/{id}/cambiar', [HabitacionController::class, 'cambiarHabitacion'])->middleware(['auth', 'verified'])->name('habitaciones.cambiarHabitacion');

Route::middleware(['auth'])->group(function () {
    Route::get('/gasto-items', [GastoItemsController::class, 'index'])->name('gasto-items.index');
    Route::get('/gasto-items/create', [GastoItemsController::class, 'create'])->name('gasto-items.create');
    Route::post('/gasto-items', [GastoItemsController::class, 'store'])->name('gasto-items.store');
    Route::get('/gasto-items/{gastoItem}/edit', [GastoItemsController::class, 'edit'])->name('gasto-items.edit');
    Route::put('/gasto-items/{gastoItem}', [GastoItemsController::class, 'update'])->name('gasto-items.update');
    Route::delete('/gasto-items/{gastoItem}', [GastoItemsController::class, 'destroy'])->name('gasto-items.destroy');
});

Route::get('/limpieza', [LimpiezaController::class, 'index'])->middleware(['auth', 'verified'])->name('limpieza.index');
Route::post('/limpieza/{id}/disponible', [LimpiezaController::class, 'marcarDisponible'])->middleware(['auth', 'verified'])->name('limpieza.disponible');



Route::get('/disponibilidad/calendario', [DisponibilidadController::class, 'calendario'])
    ->name('disponibilidad.calendario');


Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
});


require __DIR__ . '/auth.php';
