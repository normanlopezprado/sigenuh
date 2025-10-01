<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use App\Models\Hospital;

class AppServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);
        View::composer('*', function ($view) {
            $view->with('hospitals_table', Hospital::where('status', true)->get());
        });
    }
}
