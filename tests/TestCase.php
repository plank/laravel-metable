<?php

namespace Plank\Metable\Tests;

use Illuminate\Hashing\HashServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Plank\Metable\MetableServiceProvider;
use Plank\Metable\Tests\Mocks\SampleSerializable;
use ReflectionClass;

class TestCase extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->withFactories(__DIR__ . '/factories');
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
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));

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

        $app['config']->set('metable.serializableHandlerAllowedClasses', [SampleSerializable::class]);
    }

    protected function getPrivateProperty($class, $property_name)
    {
        $reflector = new ReflectionClass($class);
        $property = $reflector->getProperty($property_name);
        return $property;
    }

    protected function getPrivateMethod($class, $method_name)
    {
        $reflector = new ReflectionClass($class);
        $method = $reflector->getMethod($method_name);
        return $method;
    }

    protected function useDatabase()
    {
        $this->app->useDatabasePath(realpath(__DIR__) . '/..');
        $this->loadMigrationsFrom(
            [
                '--path' => [
                    dirname(__DIR__) . '/migrations',
                    __DIR__ . '/migrations'
                ]
            ]
        );
    }
}
