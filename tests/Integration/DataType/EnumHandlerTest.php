<?php

namespace Plank\Metable\Tests\Integration\DataType;

use Plank\Metable\DataType\BackedEnumHandler;
use Plank\Metable\DataType\PureEnumHandler;
use Plank\Metable\Tests\TestCase;

class EnumHandlerTest extends TestCase
{
    public function test_back_enum_handles_unknown_class()
    {
        $handler = new BackedEnumHandler();
        $this->assertNull($handler->unserializeValue('baz#value'));
    }

    public function test_back_enum_handles_non_enum()
    {
        $handler = new BackedEnumHandler();
        $this->assertNull($handler->unserializeValue('stdClass#value'));
    }

    public function test_pure_enum_handles_unknown_class()
    {
        $handler = new PureEnumHandler();
        $this->assertNull($handler->unserializeValue('baz#value'));
    }

    public function test_pure_enum_handles_non_enum()
    {
        $handler = new PureEnumHandler();
        $this->assertNull($handler->unserializeValue('stdClass#value'));
    }
}
