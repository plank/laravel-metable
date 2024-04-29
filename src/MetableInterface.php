<?php

namespace Plank\Metable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static Builder whereHasMeta($key)
 * @method static Builder whereDoesntHaveMeta($key)
 * @method static Builder whereHasMetaKeys(array $keys)
 * @method static Builder whereMeta(string $key, $operator, $value = null)
 * @method static Builder whereMetaNumeric(string $key, string $operator, $value)
 * @method static Builder whereMetaIn(string $key, array $values)
 * @method static Builder whereMetaNotIn(string $key, array $values)
 * @method static Builder whereMetaInNumeric(string $key, array $values)
 * @method static Builder whereMetaNotInNumeric(string $key, array $values)
 * @method static Builder whereMetaBetween(string $key, array $values)
 * @method static Builder whereMetaNotBetween(string $key, array $values)
 * @method static Builder whereMetaBetweenNumeric(string $key, array $values)
 * @method static Builder whereMetaNotBetweenNumeric(string $key, array $values)
 * @method static Builder whereMetaIsModel(string $key, string|Model $classOrInstance, string $modelId = null)
 * @method static Builder whereMetaIsNull(string $key)
 * @method static Builder orderByMeta(string $key, string $direction = 'asc', $strict = false)
 * @method static Builder orderByMetaNumeric(string $key, string $direction = 'asc', $strict = false)
 */
interface MetableInterface
{
    public function meta(): MorphMany;

    public function setMeta(string $key, mixed $value, bool $encrypted = false): void;

    public function setMetaEncrypted(string $key, mixed $value): void;

    public function setManyMeta(array $metaDictionary): void;

    public function syncMeta(iterable $array): void;

    /**
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function getMeta(string $key, mixed $default = null);

    /**
     * @return Collection<array-key, mixed>
     */
    public function getAllMeta(): Collection;

    public function hasMeta(string $key): bool;

    public function removeMeta(string $key): void;

    public function removeManyMeta(array $keys): void;

    public function purgeMeta(): void;

    public function getMetaRecord(string $key): ?Meta;
}
