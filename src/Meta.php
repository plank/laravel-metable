<?php

namespace Plank\Metable;

use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Crypt;
use Plank\Metable\DataType\Registry;
use Plank\Metable\Exceptions\SecurityException;

/**
 * Model for storing meta data.
 *
 * @property int $id
 * @property string $metable_type
 * @property int $metable_id
 * @property string $type
 * @property string $key
 * @property mixed $value
 * @property string $raw_value
 * @property null|string $string_value
 * @property null|int|float $numeric_value
 * @property null|string $hmac
 * @property Model $metable
 */
class Meta extends Model
{
    public const ENCRYPTED_PREFIX = 'encrypted:';

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
        'numeric_value',
        'hmac'
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
            $type = $this->type;
            $value = $this->attributes['value'];

            if (str_starts_with($type, self::ENCRYPTED_PREFIX)) {
                $value = $this->getEncrypter()->decrypt($value);
                $type = substr($this->type, strlen(self::ENCRYPTED_PREFIX));
            }

            $registry = $this->getDataTypeRegistry();
            $handler = $registry->getHandlerForType($type);

            if ($handler->useHmacVerification()) {
                $this->verifyHmac($value, $this->attributes['hmac']);
            }

            $this->cachedValue = $handler->unserializeValue(
                $value
            );
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
        $handler = $registry->getHandlerForType($this->attributes['type']);

        $this->attributes['value'] = $handler->serializeValue($value);
        $this->attributes['numeric_value'] = $handler->getNumericValue($value);
        $this->attributes['hmac'] = $handler->useHmacVerification()
            ? $this->computeHmac($this->attributes['value'])
            : null;

        $this->cachedValue = null;
    }

    public function encrypt(): void
    {
        if ($this->type === 'null') {
            return;
        }

        if (str_starts_with($this->type, self::ENCRYPTED_PREFIX)) {
            return;
        }

        $this->attributes['value'] = $this->getEncrypter()
            ->encrypt($this->attributes['value']);
        $this->type = self::ENCRYPTED_PREFIX . $this->type;
        $this->numeric_value = null;
    }

    public function getRawValueAttribute(): string
    {
        return $this->getRawValue();
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

    protected function verifyHmac(string $serializedValue, string $hmac): void
    {
        $expectedHash = $this->computeHmac($serializedValue);
        if (!hash_equals($expectedHash, $hmac)) {
            throw SecurityException::hmacVerificationFailed();
        }
    }

    protected function computeHmac(string $serializedValue): string
    {
        return hash_hmac('sha256', $serializedValue, config('app.key'));
    }

    protected function getEncrypter(): Encrypter
    {
        return self::$encrypter ?? Crypt::getFacadeRoot();
    }
}
