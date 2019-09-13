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
            __DIR__ . '/../config/metable.php' => config_path('metable.php'),
        ], 'config');

        if (!class_exists(CreateMetaTable::class)) {
            $this->publishes([
                __DIR__ . '/../migrations/2017_01_01_000000_create_meta_table.php' => database_path('migrations/' . date('Y_m_d_His') . '_create_meta_table.php'),
            ], 'migrations');
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
            __DIR__ . '/../config/metable.php', 'metable'
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
