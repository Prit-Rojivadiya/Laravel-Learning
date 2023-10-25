<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ( env('LOG_DB') === true || env('LOG_DB') === 'true') {
            DB::listen(function ($query) {
                $connectionName = $query->connection->getName();
                $queryTime = $query->time/1000;
                $logStr = "$connectionName ($queryTime sec): $query->sql";
                Log::info(
                    $logStr,
                    $query->bindings,
                );
            });
        }
    }
}
