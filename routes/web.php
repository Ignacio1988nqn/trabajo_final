<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HuespedController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

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


require __DIR__ . '/auth.php';
