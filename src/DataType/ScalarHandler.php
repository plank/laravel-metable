<?php

namespace Plank\Metable\DataType;

/**
 * Handle serialization of scalar values.
 */
abstract class ScalarHandler implements HandlerInterface
{
    /**
     * The name of the scalar data type.
     *
     * @var string
     */
    protected $type;

    /**
     * {@inheritdoc}
     */
    public function getDataType(): string
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function canHandleValue($value): bool
    {
        return gettype($value) == $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function serializeValue($value): string
    {
        settype($value, 'string');

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function unserializeValue(string $value)
    {
        settype($value, $this->type);

        return $value;
    }
}
