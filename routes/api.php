<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SoapWrapperController;
use App\Http\Controllers\HotelLocationController;

Route::get('/ocupacion', [SoapWrapperController::class, 'habitacionesDisponibles']);
Route::get('/hotel/real', [HotelLocationController::class, 'search']);
