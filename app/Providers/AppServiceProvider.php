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
    }
}
