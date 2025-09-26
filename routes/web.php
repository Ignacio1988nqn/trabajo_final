<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HuespedController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ReservaController;


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
});


require __DIR__ . '/auth.php';
