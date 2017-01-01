<?php

namespace Plank\Metable;

use Illuminate\Support\ServiceProvider;
use Plank\Metable\DataType\Registry;

class MetableServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/metable.php' => config_path('metable.php'),
        ], 'config');

        // if (! class_exists(CreateMediableTables::class)) {
        //     $this->publishes([
        //         __DIR__.'/../migrations/2016_06_27_000000_create_mediable_tables.php' => database_path('migrations/'.date('Y_m_d_His').'_create_mediable_tables.php'),
        //     ], 'migrations');
        // }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/metable.php', 'metable'
        );

        $this->registerDataTypeRegistry();
    }

    protected function registerDataTypeRegistry()
    {
        $this->app->singleton(Registry::class, function($app){
            $registry = new Registry;
            foreach (config('metable.datatypes') as $type => $handler) {
                $registry->setHandlerForType(new $handler, $type);
            }
            return $registry;
        });
        $this->app->alias(Registry::class, 'metable.datatype.registry');
    }
}
