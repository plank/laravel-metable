<?php

namespace Plank\Metable\DataType;

/**
 * Handle serialization of scalar values.
 */
abstract class ScalarHandler extends Handler
{
    /**
     * The name of the scalar data type.
     */
    protected function getType(): string
    {
        return $this->getDataType();
    }

    /**
     * {@inheritdoc}
     */
    public function canHandleValue($value): bool
    {
        return gettype($value) == $this->getType();
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
        settype($value, $this->getType());

        return $value;
    }
}
