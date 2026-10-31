<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

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
        // teto geral por IP para todas as ações Livewire (site e painel)
        RateLimiter::for('livewire', fn(Request $request) => Limit::perMinute(120)->by($request->ip()));

        Livewire::setUpdateRoute(fn($handle) => Route::post('/livewire/update', $handle)
            ->middleware(['web', 'throttle:livewire']));
    }
}
