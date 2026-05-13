<?php

namespace App\Providers;

use App\Listeners\LogUserLogin;
use App\Listeners\LogUserRegistered;
use App\Models\OrderModel;
use App\Observers\OrderObserver;
use App\Policies\OrderPolicy;
use Filament\Auth\Events\Registered;
use Filament\Auth\Pages\Login;
use Illuminate\Support\Facades\Event;
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
        OrderModel::observe(OrderObserver::class);
        Event::listen(Registered::class, LogUserRegistered::class);
        Event::listen(Login::class, LogUserLogin::class);
    }
}
