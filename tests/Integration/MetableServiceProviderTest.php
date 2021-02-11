<?php

namespace Plank\Metable\Tests\Integration;

use Plank\Metable\MetableServiceProvider;
use Plank\Metable\Tests\TestCase;

class MetableServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [];
    }

    public function testBootSkipsMigrations()
    {
        config()->set('metable.applyMigrations', false);
        $provider = new MetableServiceProvider(app());
        $provider->boot();
        $this->assertEmpty(app('migrator')->paths());
    }

    public function testBootAppliesMigrations()
    {
        config()->set('metable.applyMigrations', true);
        $provider = new MetableServiceProvider(app());
        $provider->boot();
        $expected = dirname(__DIR__, 2) . '/migrations';
        $this->assertContains($expected, app('migrator')->paths());
    }
}
