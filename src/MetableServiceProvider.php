<?php

namespace Plank\Metable;

use CreateMetaTable;
use Illuminate\Support\ServiceProvider;
use Plank\Metable\DataType\Registry;

/**
 * Laravel-Metable Service Provider.
 */
class MetableServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            dirname(__DIR__) . '/config/metable.php' => config_path('metable.php'),
        ], 'config');

        if (config('metable.applyMigrations', true)) {
            $this->loadMigrationsFrom(dirname(__DIR__) . '/migrations');
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/metable.php',
            'metable'
        );

        $this->registerDataTypeRegistry();
    }

    /**
     * Add the DataType Registry to the service container.
     *
     * @return void
     */
    protected function registerDataTypeRegistry(): void
    {
        $this->app->singleton(Registry::class, function () {
            $registry = new Registry();
            foreach (config('metable.datatypes') as $handler) {
                $registry->addHandler(new $handler());
            }

            return $registry;
        });
        $this->app->alias(Registry::class, 'metable.datatype.registry');
    }
}
