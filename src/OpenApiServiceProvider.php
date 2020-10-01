<?php

namespace Voice\OpenApi;

use Illuminate\Support\ServiceProvider;
use Voice\OpenApi\App\Console\Commands\OpenApi;

class OpenApiServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/asseco-open-api.php', 'asseco-open-api');
    }

    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->publishes([__DIR__ . '/../config/asseco-open-api.php' => config_path('asseco-open-api.php'),]);

        $this->app->singleton(SchemaBuilder::class);
        $this->app->singleton(Generator::class);

        if ($this->app->runningInConsole()) {
            $this->commands([OpenApi::class]);
        }
    }
}
