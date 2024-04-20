<?php

namespace Plank\Metable\Tests\Integration\DataType;

use Plank\Metable\DataType\SignedSerializeHandler;
use Plank\Metable\Tests\Mocks\SampleSerializable;
use Plank\Metable\Tests\TestCase;

class SignedSerializeHandlerTest extends TestCase
{
    public function test_it_respects_allowed_classes(): void
    {
        $handler = new SignedSerializeHandler();
        $value = new SampleSerializable(['foo' => 'bar']);
        $serialized = $handler->serializeValue($value);
        $hmac = $handler->useHmacVerification();

        config()->set('metable.signedSerializeHandlerAllowedClasses', false);
        $unserialized = $handler->unserializeValue($serialized);
        $this->assertInstanceOf(\__PHP_Incomplete_Class::class, $unserialized);

        config()->set('metable.signedSerializeHandlerAllowedClasses', [SampleSerializable::class]);
        $unserialized = $handler->unserializeValue($serialized);
        $this->assertInstanceOf(SampleSerializable::class, $unserialized);
        $this->assertEquals($value, $unserialized);
    }
}
