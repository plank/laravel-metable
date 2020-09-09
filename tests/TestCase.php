<?php

use Orchestra\Testbench\TestCase as BaseTestCase;
use Plank\Metable\MetableServiceProvider;

class TestCase extends BaseTestCase
{
    protected $metableFactory;
    protected $metaFactory;
    protected $morphFactory;

    public function setUp(): void
    {
        parent::setUp();
        $this->metableFactory = new MetableFactory();
        $this->metaFactory = new MetaFactory();
        $this->morphFactory = new MorphFactory();
    }

    protected function getPackageProviders($app)
    {
        return [
            MetableServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [];
    }

    protected function getEnvironmentSetUp($app)
    {
        date_default_timezone_set('GMT');
        //use in-memory database
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('database.connections.mysql-test', [
            'driver' => 'mysql',
            'host' => env('DB_HOST', 'localhost'),
            'database' => env('DB_DATABASE', 'metable'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', 'root'),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
        ]);
        $app['config']->set('database.default', 'testing');
    }

    protected function getPrivateProperty($class, $property_name)
    {
        $reflector = new ReflectionClass($class);
        $property = $reflector->getProperty($property_name);
        $property->setAccessible(true);

        return $property;
    }

    protected function getPrivateMethod($class, $method_name)
    {
        $reflector = new ReflectionClass($class);
        $method = $reflector->getMethod($method_name);
        $method->setAccessible(true);

        return $method;
    }

    protected function useDatabase()
    {
        $artisan = $this->app->make('Illuminate\Contracts\Console\Kernel');
        $database = $this->app['config']->get('database.default');
        $this->app->useDatabasePath(realpath(__DIR__) . '/..');
        //Remigrate all database tables
        $artisan->call('migrate:refresh', [
            '--database' => $database,
        ]);
    }
}
