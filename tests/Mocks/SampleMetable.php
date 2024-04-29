<?php

namespace Plank\Metable\Tests\Mocks;

use Illuminate\Database\Eloquent\Model;
use Plank\Metable\Metable;
use Plank\Metable\MetableAttributes;
use Plank\Metable\MetableInterface;

class SampleMetable extends Model implements MetableInterface
{
    use Metable;
    use MetableAttributes;

    protected $attributes = [
        'meta_attribute' => '',
    ];

    protected $defaultMetaValues = [
        'foo' => 'bar'
    ];

    public $metaCasts = [];

    public $methodMetaCasts = [];

    public $includeMetaInArray = true;

    public function metaCasts(): array
    {
        return $this->methodMetaCasts;
    }
}
