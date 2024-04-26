<?php

namespace Plank\Metable\Tests\Integration\Commands;

use Illuminate\Support\Facades\DB;
use Plank\Metable\DataType\ArrayHandler;
use Plank\Metable\DataType\DateTimeHandler;
use Plank\Metable\DataType\SignedSerializeHandler;
use Plank\Metable\DataType\StringHandler;
use Plank\Metable\Tests\TestCase;

class RefreshMetaTest extends TestCase
{
    public function test_it_refreshes_all_meta_values(): void
    {
        $this->useDatabase();

        config()->set('metable.datatypes', [
            StringHandler::class,
            DateTimeHandler::class,
            SignedSerializeHandler::class,
            ArrayHandler::class,
        ]);

        config()->set('metable.refreshPageSize', 2);

        $complexValue = ['a' => 'b'];

        DB::table('meta')->insert([
            [
                'metable_type' => 'foo',
                'metable_id' => 1,
                'type' => 'array',
                'key' => 'foo',
                'value' => json_encode($complexValue),
                'numeric_value' => null,
            ],
            [
                'metable_type' => 'foo',
                'metable_id' => 2,
                'type' => 'string',
                'key' => 'bar',
                'value' => 'blah',
                'numeric_value' => null,
            ],
            [
                'metable_type' => 'foo',
                'metable_id' => 3,
                'type' => 'datetime',
                'key' => 'baz',
                'value' => '2020-01-01 00:00:00.000000+0000',
                'numeric_value' => null,
            ],
        ]);

        $this->artisan('metable:refresh')
            ->expectsOutput('Refreshing meta values...')
            ->expectsOutput('Refresh complete.')
            ->assertExitCode(0);


        $result = DB::table('meta')->get();
        $this->assertCount(3, $result);

        $this->assertEquals('serialized', $result[0]->type);
        $this->assertEquals($complexValue, unserialize($result[0]->value));
        $this->assertNull($result[0]->numeric_value);

        $this->assertEquals('string', $result[1]->type);
        $this->assertEquals('blah', $result[1]->value);
        $this->assertNull($result[1]->numeric_value);

        $this->assertEquals('datetime', $result[2]->type);
        $this->assertEquals('2020-01-01 00:00:00.000000+0000', $result[2]->value);
        $this->assertEquals(1577836800, $result[2]->numeric_value);
    }
}
