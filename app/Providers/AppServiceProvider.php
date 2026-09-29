<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        // Limite la création de comptes en rafale depuis une même adresse IP.
        RateLimiter::for('inscription', fn (Request $requete) => Limit::perMinute(5)
            ->by($requete->ip())
            ->response(fn () => back()
                ->withErrors(['courriel' => 'Trop de tentatives d’inscription. Réessayez dans une minute.'])
                ->withInput($requete->only('courriel'))));
    }
}
