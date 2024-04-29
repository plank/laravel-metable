<?php

namespace Plank\Metable\DataType;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTimeInterface;

/**
 * Handle serialization of DateTimeInterface objects.
 */
class DateTimeImmutableHandler implements HandlerInterface
{
    /**
     * The date format to use for serializing.
     *
     * @var string
     */
    public const FORMAT = 'Y-m-d H:i:s.uO';

    /**
     * {@inheritdoc}
     */
    public function getDataType(): string
    {
        return 'datetime_immutable';
    }

    /**
     * {@inheritdoc}
     */
    public function canHandleValue(mixed $value): bool
    {
        return $value instanceof \DateTimeImmutable;
    }

    /**
     * {@inheritdoc}
     */
    public function serializeValue(mixed $value): string
    {
        return $value->format(self::FORMAT);
    }

    /**
     * {@inheritdoc}
     */
    public function unserializeValue(string $serializedValue): mixed
    {
        return CarbonImmutable::createFromFormat(self::FORMAT, $serializedValue);
    }

    public function getNumericValue(mixed $value): null|int|float
    {
        return $value instanceof DateTimeInterface
            ? $value->getTimestamp()
            : null;
    }

    public function useHmacVerification(): bool
    {
        return false;
    }
}
