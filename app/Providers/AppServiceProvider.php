<?php

namespace App\Providers;

use App\Models\Turno;
use App\Observers\TurnoObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Turno::observe(TurnoObserver::class);
    }
}