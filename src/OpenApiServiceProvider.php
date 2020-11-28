<?php

namespace Asseco\OpenApi;

use Asseco\OpenApi\App\Console\Commands\OpenApi;
use Asseco\OpenApi\Specification\Document;
use Illuminate\Support\ServiceProvider;

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
        $this->publishes([__DIR__ . '/../config/asseco-open-api.php' => config_path('asseco-open-api.php')]);

        $this->app->singleton(Document::class);
        $this->app->singleton(SchemaGenerator::class);

        if ($this->app->runningInConsole()) {
            $this->commands([OpenApi::class]);
        }
    }
}
