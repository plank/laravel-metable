<?php

namespace Plank\Metable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Plank\Metable\DataType\Registry;

/**
 * Model for storing meta data.
 *
 * @property int $id
 * @property string $metable_type
 * @property int $metable_id
 * @property string $type
 * @property string $key
 * @property mixed $value
 * @property null|string $string_value
 * @property null|int|float $numeric_value
 * @property Model $metable
 */
class Meta extends Model
{
    /**
     * {@inheritdoc}
     */
    public $timestamps = false;

    /**
     * {@inheritdoc}
     */
    protected $table = 'meta';

    /**
     * {@inheritdoc}
     */
    protected $guarded = [
        'id',
        'metable_type',
        'metable_id',
        'type',
        'string_value',
        'numeric_value'
    ];

    /**
     * {@inheritdoc}
     */
    protected $attributes = [
        'type' => 'null',
        'value' => '',
    ];

    /**
     * Cache of unserialized value.
     *
     * @var mixed
     */
    protected mixed $cachedValue;

    /**
     * Metable Relation.
     *
     * @return MorphTo
     */
    public function metable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Accessor for value.
     *
     * Will unserialize the value before returning it.
     *
     * Successive access will be loaded from cache.
     *
     * @return mixed
     * @throws Exceptions\DataTypeException
     */
    public function getValueAttribute(): mixed
    {
        if (!isset($this->cachedValue)) {
            $this->cachedValue = $this->getDataTypeRegistry()
                ->getHandlerForType($this->type)
                ->unserializeValue($this->attributes['value']);
        }

        return $this->cachedValue;
    }

    /**
     * Mutator for value.
     *
     * The `type` attribute will be automatically updated to match the datatype of the input.
     *
     * @param mixed $value
     * @throws Exceptions\DataTypeException
     */
    public function setValueAttribute(mixed $value): void
    {
        $registry = $this->getDataTypeRegistry();

        $this->attributes['type'] = $registry->getTypeForValue($value);
        $handler = $registry->getHandlerForType($this->type);

        $this->attributes['value'] = $handler->serializeValue($value);
        $this->attributes['string_value'] = $handler->getStringValue($value);
        $this->attributes['numeric_value'] = $handler->getNumericValue($value);

        $this->cachedValue = null;
    }

    /**
     * Retrieve the underlying serialized value.
     *
     * @return string
     */
    public function getRawValue(): string
    {
        return $this->attributes['value'];
    }

    /**
     * Load the datatype Registry from the container.
     *
     * @return Registry
     */
    protected function getDataTypeRegistry(): Registry
    {
        return app('metable.datatype.registry');
    }
}
