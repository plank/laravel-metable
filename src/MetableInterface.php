<?php

namespace Plank\Metable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Plank\Metable\Meta;

/**
 * @method static Builder whereHasMeta($key): void
 * @method static Builder whereDoesntHaveMeta($key)
 * @method static Builder whereHasMetaKeys(array $keys)
 * @method static Builder whereMeta(string $key, $operator, $value = null)
 * @method static Builder whereMetaNumeric(string $key, string $operator, $value)
 * @method static Builder whereMetaIn(string $key, array $values)
 * @method static Builder orderByMeta(string $key, string $direction = 'asc', $strict = false)
 * @method static Builder orderByMetaNumeric(string $key, string $direction = 'asc', $strict = false)
 */
interface MetableInterface
{
    public function meta(): MorphMany;

    public function setMeta(string $key, mixed $value): void;

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
