<?php

namespace Devsfort\Location;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use PhpParser\Node\Scalar\MagicConst\Dir;

class LocationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        app()->bind('DevsfortLocation', function () {
            return new DevsfortLocation();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Load Views, Migrations and Routes
        // $this->loadViewsFrom(__DIR__ . '/views', 'Devsfort');
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->loadRoutes();

        // Publishes
        $this->setPublishes();
    }

    /**
     * Publishing the files that the user may override.
     *
     * @return void
     */
    protected function setPublishes()
    {
        // Config


        $this->publishes([
            __DIR__ . '/config/devslocation.php' => config_path('devslocation.php')
        ], 'devslocation-config');
    }
    /**
     * Group the routes and set up configurations to load them.
     *
     * @return void
     */
    protected function loadRoutes()
    {
        Route::group($this->routesConfigurations(), function () {
            $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
        });
    }

    /**
     * Routes configurations.
     *
     * @return array
     */
    private function routesConfigurations()
    {
        return [
            'prefix' => config('devslocation.path'),
            'namespace' =>  config('devslocation.namespace'),
            'middleware' => ['api'],
        ];
    }
}