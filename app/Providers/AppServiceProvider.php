<?php

namespace App\Providers;

use App\Services\CartService;
use App\Services\Notifications\MailOrderNotificationChannel;
use App\Services\Notifications\OrderNotificationService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CartService::class);

        $this->app->singleton(OrderNotificationService::class, function ($app) {
            return new OrderNotificationService([
                $app->make(MailOrderNotificationChannel::class),
            ]);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
