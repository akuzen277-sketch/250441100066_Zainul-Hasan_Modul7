<?php

namespace App\Providers;

use App\Models\Peminjaman;
use App\Models\User;
use App\Policies\PeminjamanPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
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
        if (config('app.env') === 'production' || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) || isset($_SERVER['VERCEL']) || isset($_ENV['VERCEL'])) {
            URL::forceScheme('https');
        }

        Gate::define(
            'kelola-buku',
            fn (User $user): bool => $user->hasAnyRole(['admin', 'petugas'])
        );

        Gate::policy(Peminjaman::class, PeminjamanPolicy::class);
    }
}
