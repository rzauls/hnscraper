<?php

namespace App\Providers;

use App\Interfaces\HNClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $default = config('hn.default');
        $hnClient = config("hn.{$default}.class");

        $this->app->bind(HNClient::class, $hnClient);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
