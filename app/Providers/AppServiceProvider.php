<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;


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
        Gate::define('view-report', function ($user, string $type) {
            return match ($user->level) {
                'Sarpras', 'Admin', 'Rektor' => true, // Rektor = Eksekutif, lihat semua utk oversight
                'Kalab', 'Kaprodi' => $type === 'assets', // scope: cuma laporan aset (nanti difilter ke unit sendiri)
                'Tim Pemeliharaan' => $type === 'maintenance',
                'Keuangan' => $type === 'financial',
                default => false,
            };
        });

        //
        \URL::forceRootUrl(\Config::get('app.url'));
        // And this if you wanna handle https URL scheme
        // It's not usefull for http://www.example.com, it's just to make it more independant from the constant value
        if (\Str::contains(\Config::get('app.url'), 'https://')) {
            \URL::forceScheme('https');
            //use \URL:forceSchema('https') if you use laravel < 5.4
        }
    }
}
