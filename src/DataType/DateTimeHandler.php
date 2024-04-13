<?php

namespace Plank\Metable\DataType;

use Carbon\Carbon;
use DateTimeInterface;

/**
 * Handle serialization of DateTimeInterface objects.
 */
class DateTimeHandler implements HandlerInterface
{
    /**
     * The date format to use for serializing.
     *
     * @var string
     */
    protected $format = 'Y-m-d H:i:s.uO';

    /**
     * {@inheritdoc}
     */
    public function getDataType(): string
    {
        return 'datetime';
    }

    /**
     * {@inheritdoc}
     */
    public function canHandleValue(mixed $value): bool
    {
        return $value instanceof DateTimeInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function serializeValue(mixed $value): string
    {
        return $value->format($this->format);
    }

    /**
     * {@inheritdoc}
     */
    public function unserializeValue(string $serializedValue): mixed
    {
        return Carbon::createFromFormat($this->format, $serializedValue);
    }
}
