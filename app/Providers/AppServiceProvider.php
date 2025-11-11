<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use App\Models\MenuUsuario;
use Illuminate\Support\Facades\Auth;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Inertia::share('menu', function () {
            $user = Auth::user();
            if (!$user) return [];

            return MenuUsuario::where('rol', $user->rol)
                ->whereNull('padre_id')
                ->where('activo', true)
                ->with('hijos')
                ->orderBy('orden')
                ->get();
        });
        Vite::prefetch(concurrency: 3);
    }
}
