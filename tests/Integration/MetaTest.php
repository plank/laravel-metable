<?php

namespace Plank\Metable\Tests\Integration;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Plank\Metable\Exceptions\SecurityException;
use Plank\Metable\Meta;
use Plank\Metable\Tests\TestCase;

class MetaTest extends TestCase
{
    public function test_it_can_get_and_set_value(): void
    {
        $meta = $this->makeMeta();

        $meta->value = 'foo';

        $this->assertEquals('foo', $meta->value);
        $this->assertEquals('foo', $meta->raw_value);
        $this->assertEquals('string', $meta->type);
    }

    public function test_it_exposes_its_serialized_value(): void
    {
        $meta = $this->makeMeta();
        $meta->value = 123;

        $this->assertEquals('123', $meta->getRawValue());
    }

    public function test_it_caches_unserialized_value(): void
    {
        $meta = $this->makeMeta();
        $meta->value = 'foo';
        $this->assertEquals('foo', $meta->value);

        $meta->setRawAttributes(['value' => 'bar'], true);

        $this->assertEquals('foo', $meta->value);
        $this->assertEquals('bar', $meta->getRawValue());
    }

    public function test_it_clears_cache_on_set(): void
    {
        $meta = $this->makeMeta();
        $meta->value = 'foo';
        $this->assertEquals('foo', $meta->value);

        $meta->value = 'bar';

        $this->assertEquals('bar', $meta->value);
    }

    public function test_it_can_get_its_model_relation(): void
    {
        $meta = $this->makeMeta();

        $relation = $meta->metable();

        $this->assertInstanceOf(MorphTo::class, $relation);
        $this->assertEquals('metable_type', $relation->getMorphType());
        $this->assertEquals('metable_id', $relation->getForeignKeyName());
    }

    public function test_it_verifies_hmac(): void
    {
        $this->expectException(SecurityException::class);
        $meta = $this->makeMeta();
        $meta->type = 'serialized';
        $meta->value = ['foo'];
        $this->assertEquals('serialized', $meta->type);
        $meta->hmac = hash_hmac('sha256', 'foo', 'badsecret');
        $meta->getValueAttribute();
    }

    public function test_it_can_encrypt_its_value(): void
    {
        $meta = $this->makeMeta();
        $meta->value = 'foo';
        $meta->hmac = $hmac = random_bytes(64);

        $meta->encrypt();

        $this->assertEquals('foo', $meta->value);
        $this->assertNotEquals('foo', $meta->raw_value);
        $this->assertEquals($hmac, $meta->hmac);
        $this->assertEquals('encrypted:string', $meta->type);
        $this->assertNull($meta->numeric_value);


        $rawValue = $meta->getRawValue();
        // should not re-encrypt
        $meta->encrypt();
        $this->assertEquals($rawValue, $meta->getRawValue());
        $this->assertEquals('encrypted:string', $meta->type);
    }

    private function makeMeta(array $attributes = []): Meta
    {
        return factory(Meta::class)->make($attributes);
    }
}
