<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

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
        Paginator::useBootstrapFive();

        \Illuminate\Support\Facades\View::composer('layouts.app', function ($view) {
            if (auth()->check()) {
                $unconfirmedAlert = \App\Models\AppAlert::whereHas('users', function ($q) {
                    $q->where('user_id', auth()->id())
                      ->whereNull('confirmed_at');
                })->latest()->first();
                
                $view->with('unconfirmedAlert', $unconfirmedAlert);
            }
        });
    }
}
