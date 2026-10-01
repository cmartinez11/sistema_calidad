<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        // Definición de Gate para permisos de escritura (bloquea a Gerencia)
        Gate::define('write', function (User $user) {
            return $user->canWrite();
        });

        // Directiva Blade personalizada @canWrite ... @endcanWrite
        Blade::if('canWrite', function () {
            return auth()->check() && auth()->user()->canWrite();
        });

        // Directiva Blade personalizada @role('gerencia') ... @endrole
        Blade::if('role', function ($roles) {
            return auth()->check() && auth()->user()->hasRole($roles);
        });

        // Directiva Blade personalizada @unlessrole('gerencia') ... @endunlessrole
        Blade::if('unlessrole', function ($roles) {
            return auth()->check() && !auth()->user()->hasRole($roles);
        });
    }
}
