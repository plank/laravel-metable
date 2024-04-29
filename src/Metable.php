<?php

namespace Plank\Metable;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Query\JoinClause;
use Plank\Metable\DataType\HandlerInterface;
use Plank\Metable\DataType\Registry;
use Plank\Metable\Exceptions\CastException;

/**
 * Trait for giving Eloquent models the ability to handle Meta.
 *
 * @property Collection<Meta> $meta
 * @method static Builder whereHasMeta(string|string[] $key): void
 * @method static Builder whereDoesntHaveMeta(string|string[] $key)
 * @method static Builder whereHasMetaKeys(array $keys)
 * @method static Builder whereMeta(string $key, mixed $operator, mixed $value = null)
 * @method static Builder whereMetaNumeric(string $key, mixed $operator, mixed $value = null)
 * @method static Builder whereMetaIn(string $key, array $values)
 * @method static Builder whereMetaInNumeric(string $key, array $values)
 * @method static Builder whereMetaNotIn(string $key, array $values)
 * @method static Builder whereMetaNotInNumeric(string $key, array $values)
 * @method static Builder whereMetaBetween(string $key, mixed $min, mixed $max, bool $not = false)
 * @method static Builder whereMetaBetweenNumeric(string $key, mixed $min, mixed $max, bool $not = false)
 * @method static Builder whereMetaNotBetween(string $key, mixed $min, mixed $max)
 * @method static Builder whereMetaNotBetweenNumeric(string $key, mixed $min, mixed $max)
 * @method static Builder whereMetaIsNull(string $key)
 * @method static Builder whereMetaIsModel(string $key, Model|string $classOrInstance, null|int|string $id = null)
 * @method static Builder orderByMeta(string $key, string $direction = 'asc', bool $strict = false)
 * @method static Builder orderByMetaNumeric(string $key, string $direction = 'asc', bool $strict = false)
 */
trait Metable
{
    /**
     * @var Collection<Meta>
     */
    private $indexedMetaCollection;

    private array $mergedMetaCasts = [];


    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * Initialize the trait.
     *
     * @return void
     */
    public static function bootMetable(): void
    {
        // delete all attached meta on deletion
        static::deleted(function (self $model) {
            if (method_exists($model, 'isForceDeleting') && !$model->isForceDeleting()) {
                return;
            }
            $model->purgeMeta();
        });
    }

    /**
     * Relationship to the `Meta` model.
     *
     * @return MorphMany
     */
    public function meta(): MorphMany
    {
        return $this->morphMany($this->getMetaClassName(), 'metable');
    }

    /**
     * Add or update the value of the `Meta` at a given key.
     *
     * @param string $key
     * @param mixed $value
     */
    public function setMeta(string $key, mixed $value, bool $encrypt = false): void
    {
        if ($this->hasMeta($key)) {
            $meta = $this->getMetaRecord($key);
            $meta->setAttribute('value', $this->castMetaValueIfNeeded($key, $value));
            if ($encrypt || $this->hasEncryptedMetaCast($key)) {
                $meta->encrypt();
            }
            $meta->save();
        } else {
            $meta = $this->makeMeta($key, $value, $encrypt);
            $this->meta()->save($meta);
            $this->meta[] = $meta;
            $this->indexedMetaCollection[$key] = $meta;
        }
    }

    public function setMetaEncrypted(string $key, mixed $value): void
    {
        $this->setMeta($key, $value, true);
    }

    /**
     * Add or update many `Meta` values.
     *
     * @param array<string,mixed> $metaDictionary key-value pairs
     *
     * @return void
     */
    public function setManyMeta(array $metaDictionary): void
    {
        if (empty($metaDictionary)) {
            return;
        }

        $builder = $this->meta()->getBaseQuery();
        $needReload = $this->relationLoaded('meta');

        $metaModels = new Collection();
        foreach ($metaDictionary as $key => $value) {
            $metaModels[$key] = $this->makeMeta($key, $value);
        }

        $builder->upsert(
            $metaModels->map(function (Meta $model) {
                return $model->getAttributesForInsert();
            })->all(),
            ['metable_type', 'metable_id', 'key'],
            ['type', 'value', 'numeric_value', 'hmac']
        );

        if ($needReload) {
            // reload media relation and indexed cache
            $this->load('meta');
        }
    }

    /**
     * Replace all associated `Meta` with the keys and values provided.
     *
     * @param iterable $array
     *
     * @return void
     */
    public function syncMeta(iterable $array): void
    {
        $meta = [];

        foreach ($array as $key => $value) {
            $meta[$key] = $this->makeMeta($key, $value);
        }

        $this->meta()->delete();
        $this->meta()->saveMany($meta);

        // Update cached relationship.
        $collection = $this->getMetaInstance()->newCollection($meta);
        $this->setRelation('meta', $collection);
    }

    /**
     * Retrieve the value of the `Meta` at a given key.
     *
     * @param string $key
     * @param mixed $default Fallback value if no Meta is found.
     *
     * @return mixed
     */
    public function getMeta(string $key, mixed $default = null): mixed
    {
        if ($this->hasMeta($key)) {
            return $this->getMetaRecord($key)->getAttribute('value');
        }

        // If we have only one argument provided (i.e. default is not set)
        // then we check the model for the defaultMetaValues
        if (func_num_args() == 1 && $this->hasDefaultMetaValue($key)) {
            return $this->getDefaultMetaValue($key);
        }

        return $default;
    }

    /**
     * Check if the default meta array exists and the key is set
     *
     * @param string $key
     * @return boolean
     */
    protected function hasDefaultMetaValue(string $key): bool
    {
        return array_key_exists($key, $this->getAllDefaultMeta());
    }

    /**
     * Get the default meta value by key
     *
     * @param string $key
     * @return mixed
     */
    protected function getDefaultMetaValue(string $key): mixed
    {
        return $this->getAllDefaultMeta()[$key];
    }

    /**
     * Retrieve all meta attached to the model as a key/value map.
     *
     * @return \Illuminate\Support\Collection<int, mixed>
     */
    public function getAllMeta(): \Illuminate\Support\Collection
    {
        return collect($this->getAllDefaultMeta())->merge(
            $this->getMetaCollection()->toBase()->map(
                fn (Meta $meta) => $meta->getAttribute('value')
            )
        );
    }

    /**
     * Check if a `Meta` has been set at a given key.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasMeta(string $key): bool
    {
        return $this->getMetaCollection()->has($key);
    }

    /**
     * Delete the `Meta` at a given key.
     *
     * @param string $key
     *
     * @return void
     */
    public function removeMeta(string $key): void
    {
        if ($this->hasMeta($key)) {
            $this->getMetaCollection()->pull($key)->delete();
        }
    }

    /**
     * Delete many `Meta` keys.
     *
     * @param string[] $keys
     *
     * @return void
     */
    public function removeManyMeta(array $keys): void
    {
        $relation = $this->meta();
        $relation->newQuery()
            ->where($relation->getMorphType(), $this->getMorphClass())
            ->where($relation->getForeignKeyName(), $this->getKey())
            ->whereIn('key', $keys)
            ->delete();

        if ($this->relationLoaded('meta')) {
            $this->load('meta');
        }
    }

    /**
     * Delete all meta attached to the model.
     *
     * @return void
     */
    public function purgeMeta(): void
    {
        $this->meta()->delete();
        $this->setRelation('meta', $this->getMetaInstance()->newCollection());
    }

    /**
     * Retrieve the `Meta` model instance attached to a given key.
     *
     * @param string $key
     *
     * @return Meta|null
     */
    public function getMetaRecord(string $key): ?Meta
    {
        return $this->getMetaCollection()->get($key);
    }

    /**
     * Query scope to restrict the query to records which have `Meta` attached to a given key.
     *
     * If an array of keys is passed instead, will restrict the query to records having one or more Meta with any of the keys.
     *
     * @param Builder $q
     * @param string|string[] $key
     *
     * @return void
     */
    public function scopeWhereHasMeta(Builder $q, string|array $key): void
    {
        $q->whereHas('meta', function (Builder $q) use ($key) {
            $q->whereIn('key', (array)$key);
        });
    }

    /**
     * Query scope to restrict the query to records which doesnt have `Meta` attached to a given key.
     *
     * If an array of keys is passed instead, will restrict the query to records having one or more Meta with any of the keys.
     *
     * @param Builder $q
     * @param string|string[] $key
     *
     * @return void
     */
    public function scopeWhereDoesntHaveMeta(Builder $q, string|array $key): void
    {
        $q->whereDoesntHave('meta', function (Builder $q) use ($key) {
            $q->whereIn('key', (array)$key);
        });
    }

    /**
     * Query scope to restrict the query to records which have `Meta` for all of the provided keys.
     *
     * @param Builder $q
     * @param array $keys
     *
     * @return void
     */
    public function scopeWhereHasMetaKeys(Builder $q, array $keys): void
    {
        $q->whereHas(
            'meta',
            function (Builder $q) use ($keys) {
                $q->whereIn('key', $keys);
            },
            '=',
            count($keys)
        );
    }

    /**
     * Query scope to restrict the query to records which have `Meta` with a specific key and value.
     *
     * If the `$value` parameter is omitted, the $operator parameter will be considered the value.
     *
     * Values will be serialized to a string before comparison. If using the `>`, `>=`, `<`, or `<=` comparison operators, note that the value will be compared as a string. If comparing numeric values, use `Metable::scopeWhereMetaNumeric()` instead.
     *
     * @param Builder $q
     * @param string $key
     * @param mixed $operator
     * @param mixed $value
     *
     * @return void
     */
    public function scopeWhereMeta(
        Builder $q,
        string $key,
        mixed $operator,
        mixed $value = null
    ): void {
        // Shift arguments if no operator is present.
        if (!isset($value)) {
            $value = $operator;
            $operator = '=';
        }

        $stringValue = $this->valueToString($value);
        $q->whereHas(
            'meta',
            function (Builder $q) use ($key, $operator, $stringValue, $value) {
                $q->where('key', $key);
                [
                    $needPartialMatch,
                    $needExactMatch
                ] = $this->determineQueryValueMatchTypes($q, [$stringValue]);

                if ($needPartialMatch) {
                    $indexLength = (int)config('metable.stringValueIndexLength', 255);
                    $q->where(
                        $q->raw("SUBSTR(value, 1, $indexLength)"),
                        $operator,
                        substr($stringValue, 0, $indexLength)
                    );
                }

                if ($needExactMatch) {
                    $q->where('value', $operator, $stringValue);
                }

                // null and empty string look the same in the database,
                // use the type column to differentiate.
                if ($value === null) {
                    $q->where('type', 'null');
                } elseif ($value === '') {
                    $q->where('type', '!=', 'null');
                }
            }
        );
    }

    /**
     * Query scope to restrict the query to records which have `Meta` with a specific key and numeric value.
     *
     * Performs numeric comparison instead of string comparison.
     *
     * @param Builder $q
     * @param string $key
     * @param mixed|string $operator
     * @param mixed $value
     *
     * @return void
     */
    public function scopeWhereMetaNumeric(
        Builder $q,
        string $key,
        mixed $operator,
        mixed $value = null
    ): void {
        // Shift arguments if no operator is present.
        if (!isset($value)) {
            $value = $operator;
            $operator = '=';
        }

        $numericValue = $this->valueToNumeric($value);
        $q->whereHas('meta', function (Builder $q) use ($key, $operator, $numericValue) {
            $q->where('key', $key);
            $q->where('numeric_value', $operator, $numericValue);
        });
    }

    public function scopeWhereMetaBetween(
        Builder $q,
        string $key,
        mixed $min,
        mixed $max,
        bool $not = false
    ): void {
        $min = $this->valueToString($min);
        $max = $this->valueToString($max);

        $q->whereHas(
            'meta',
            function (Builder $q) use ($key, $min, $max, $not) {
                $q->where('key', $key);

                [
                    $needPartialMatch,
                    $needExactMatch
                ] = $this->determineQueryValueMatchTypes($q, [$min, $max]);

                if ($needPartialMatch) {
                    $indexLength = (int)config('metable.stringValueIndexLength', 255);
                    $q->whereBetween(
                        $q->raw("SUBSTR(value, 1, $indexLength)"),
                        [
                            substr($min, 0, $indexLength),
                            substr($max, 0, $indexLength)
                        ],
                        'and',
                        $not
                    );
                }
                if ($needExactMatch) {
                    $q->whereBetween('value', [$min, $max], 'and', $not);
                }
            }
        );
    }

    public function scopeWhereMetaNotBetween(
        Builder $q,
        string $key,
        mixed $min,
        mixed $max,
    ): void {
        $this->scopeWhereMetaBetween($q, $key, $min, $max, true);
    }

    public function scopeWhereMetaBetweenNumeric(
        Builder $q,
        string $key,
        mixed $min,
        mixed $max,
        bool $not = false
    ): void {
        $min = $this->valueToNumeric($min);
        $max = $this->valueToNumeric($max);

        $q->whereHas('meta', function (Builder $q) use ($key, $min, $max, $not) {
            $q->where('key', $key);
            $q->whereBetween('numeric_value', [$min, $max], 'and', $not);
        });
    }

    public function scopeWhereMetaNotBetweenNumeric(
        Builder $q,
        string $key,
        mixed $min,
        mixed $max
    ): void {
        $this->scopeWhereMetaBetweenNumeric($q, $key, $min, $max, true);
    }

    /**
     * Query scope to restrict the query to records which have `Meta` with a specific key and a `null` value.
     * @param Builder $q
     * @param string $key
     * @return void
     */
    public function scopeWhereMetaIsNull(Builder $q, string $key): void
    {
        $this->scopeWhereMeta($q, $key, null);
    }

    public function scopeWhereMetaIsModel(
        Builder $q,
        string $key,
        Model|string $classOrInstance,
        null|int|string $id = null
    ): void {
        if ($classOrInstance instanceof Model) {
            $id = $classOrInstance->getKey();
            $classOrInstance = get_class($classOrInstance);
        }
        $value = $classOrInstance;
        if ($id) {
            $value .= '#' . $id;
        } else {
            $value .= '%';
        }

        $this->scopeWhereMeta($q, $key, 'like', $value);
    }

    /**
     * Query scope to restrict the query to records which have `Meta` with a specific key and a value within a specified set of options.
     *
     * @param Builder $q
     * @param string $key
     * @param array $values
     * @param bool $not
     *
     * @return void
     */
    public function scopeWhereMetaIn(
        Builder $q,
        string $key,
        array $values,
        bool $not = false
    ): void {
        $values = array_map(function ($val) use ($key) {
            return $this->valueToString($val);
        }, $values);

        $q->whereHas('meta', function (Builder $q) use ($key, $values, $not) {
            $q->where('key', $key);

            [
                $needPartialMatch,
                $needExactMatch
            ] = $this->determineQueryValueMatchTypes($q, $values);
            if ($needPartialMatch) {
                $indexLength = (int)config('metable.stringValueIndexLength', 255);
                $q->whereIn(
                    $q->raw("SUBSTR(value, 1, $indexLength)"),
                    array_map(
                        fn ($val) => substr($val, 0, $indexLength),
                        $values
                    ),
                    'and',
                    $not
                );
            }

            if ($needExactMatch) {
                $q->whereIn('value', $values, 'and', $not);
            }
        });
    }

    public function scopeWhereMetaNotIn(
        Builder $q,
        string $key,
        array $values
    ): void {
        $this->scopeWhereMetaIn($q, $key, $values, true);
    }

    public function scopeWhereMetaInNumeric(
        Builder $q,
        string $key,
        array $values,
        bool $not = false
    ): void {
        $values = array_map(function ($val) use ($key) {
            return $this->valueToNumeric($val);
        }, $values);

        $q->whereHas('meta', function (Builder $q) use ($key, $values, $not) {
            $q->where('key', $key);
            $q->whereIn('numeric_value', $values, 'and', $not);
        });
    }

    public function scopeWhereMetaNotInNumeric(
        Builder $q,
        string $key,
        array $values
    ): void {
        $this->scopeWhereMetaInNumeric($q, $key, $values, true);
    }

    /**
     * Query scope to order the query results by the string value of an attached meta.
     *
     * @param Builder $q
     * @param string $key
     * @param string $direction
     * @param bool $strict if true, will exclude records that do not have meta for the provided `$key`.
     *
     * @return void
     */
    public function scopeOrderByMeta(
        Builder $q,
        string $key,
        string $direction = 'asc',
        bool $strict = false
    ): void {
        $table = $this->joinMetaTable($q, $key, $strict ? 'inner' : 'left');

        [$needPartialMatch] = $this->determineQueryValueMatchTypes($q, []);
        if ($needPartialMatch) {
            $indexLength = (int)config('metable.stringValueIndexLength', 255);
            $q->orderBy(
                $q->raw("SUBSTR({$table}.value, 1, $indexLength)"),
                $direction
            );
        }

        $q->orderBy("{$table}.value", $direction);
    }

    /**
     * Query scope to order the query results by the numeric value of an attached meta.
     *
     * @param Builder $q
     * @param string $key
     * @param string $direction
     * @param bool $strict if true, will exclude records that do not have meta for the provided `$key`.
     *
     * @return void
     */
    public function scopeOrderByMetaNumeric(
        Builder $q,
        string $key,
        string $direction = 'asc',
        bool $strict = false
    ): void {
        $table = $this->joinMetaTable($q, $key, $strict ? 'inner' : 'left');
        $q->orderBy("{$table}.numeric_value", $direction);
    }

    /**
     * Join the meta table to the query.
     *
     * @param Builder $q
     * @param string $key
     * @param string $type Join type.
     *
     * @return string
     */
    private function joinMetaTable(Builder $q, string $key, string $type = 'left'): string
    {
        $relation = $this->meta();
        $metaTable = $relation->getRelated()->getTable();

        // Create an alias for the join, to allow the same
        // table to be joined multiple times for different keys.
        $alias = $metaTable . '__' . $key;

        // If no explicit select columns are specified,
        // avoid column collision by excluding meta table from select.
        if (!$q->getQuery()->columns) {
            $q->select($this->getTable() . '.*');
        }

        // Join the meta table to the query
        $q->join(
            "{$metaTable} as {$alias}",
            function (JoinClause $q) use ($relation, $key, $alias) {
                $q->on(
                    $relation->getQualifiedParentKeyName(),
                    '=',
                    $alias . '.' . $relation->getForeignKeyName()
                )
                    ->where($alias . '.key', '=', $key)
                    ->where(
                        $alias . '.' . $relation->getMorphType(),
                        '=',
                        $this->getMorphClass()
                    );
            },
            null,
            null,
            $type
        );

        // Return the alias so that the calling context can
        // reference the table.
        return $alias;
    }

    /**
     * fetch all meta for the model, if necessary.
     *
     * In Laravel versions prior to 5.3, relations that are lazy loaded by the
     * `getRelationFromMethod()` method ( invoked by the `__get()` magic method)
     * are not passed through the `setRelation()` method, so we load the relation
     * manually.
     *
     * @return mixed
     */
    private function getMetaCollection(): mixed
    {
        // load meta relation if not loaded.
        if (!$this->relationLoaded('meta')) {
            $this->setRelation('meta', $this->meta()->get());
        }

        // reindex by key for quicker lookups if necessary.
        if ($this->indexedMetaCollection === null) {
            $this->indexedMetaCollection = $this->meta->keyBy('key');
        }

        return $this->indexedMetaCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function setRelation($relation, $value)
    {
        $this->indexedMetaCollection = null;
        return parent::setRelation($relation, $value);
    }

    /**
     * Set the entire relations array on the model.
     *
     * @param array $relations
     * @return $this
     */
    public function setRelations(array $relations)
    {
        if (isset($relations['meta'])) {
            // clear the indexed cache
            $this->indexedMetaCollection = null;
        }

        return parent::setRelations($relations);
    }

    /**
     * Retrieve the FQCN of the class to use for Meta models.
     *
     * @return class-string<Meta>
     */
    protected function getMetaClassName(): string
    {
        return config('metable.model', Meta::class);
    }

    protected function getMetaInstance(): Meta
    {
        $class = $this->getMetaClassName();
        return new $class;
    }

    /**
     * Create a new `Meta` record.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return Meta
     */
    protected function makeMeta(
        string $key = null,
        mixed $value = null,
        bool $encrypt = false
    ): Meta {
        $meta = $this->getMetaInstance();
        $meta->key = $key;
        $meta->value = $this->castMetaValueIfNeeded($key, $value);
        $meta->metable_type = $this->getMorphClass();
        $meta->metable_id = $this->getKey();

        if ($encrypt || $this->hasEncryptedMetaCast($key)) {
            $meta->encrypt();
        }

        return $meta;
    }

    protected function getAllDefaultMeta(): array
    {
        return property_exists($this, 'defaultMetaValues')
            ? $this->defaultMetaValues
            : [];
    }

    protected function hasEncryptedMetaCast(string $key): bool
    {
        $cast = $this->getCastForMetaKey($key);
        return $cast === 'encrypted'
            || str_starts_with((string)$cast, 'encrypted:');
    }

    protected function castMetaValueIfNeeded(string $key, mixed $value): mixed
    {
        $cast = $this->getCastForMetaKey($key);
        if ($cast === null || $value === null) {
            return $value;
        }

        if ($cast == 'encrypted') {
            return $value;
        }

        if (str_starts_with($cast, 'encrypted:')) {
            $cast = substr($cast, 10);
        }

        return $this->castMetaValue($key, $value, $cast);
    }

    protected function castMetaValue(string $key, mixed $value, string $cast): mixed
    {
        if ($cast == 'array' || $cast == 'object') {
            return $this->castMetaToJson($cast, $value);
        }

        if ($cast == 'hashed') {
            return $this->castAttributeAsHashedString($key, $value);
        }

        if ($cast == 'collection' || str_starts_with($cast, 'collection:')) {
            return $this->castMetaToCollection($cast, $value);
        }

        if (class_exists($cast)
            && !is_a($cast, Castable::class, true)
            && $cast != 'datetime'
        ) {
            return $this->castMetaToClass($value, $cast);
        }

        // leverage Eloquent built-in casting functionality
        $castKey = "meta.$key";
        $this->casts[$castKey] = $cast;
        $value = $this->castAttribute($castKey, $value);

        // cleanup to avoid polluting the model's casts
        unset($this->casts[$castKey]);
        unset($this->attributeCastCache[$castKey]);
        unset($this->classCastCache[$castKey]);

        return $value;
    }

    protected function getCastForMetaKey(string $key): ?string
    {
        if (isset($this->mergedMetaCasts[$key])) {
            return $this->mergedMetaCasts[$key];
        }

        if (method_exists($this, 'metaCasts')) {
            $casts = $this->metaCasts();
            if (isset($casts[$key])) {
                return $casts[$key];
            }
        }

        if (property_exists($this, 'metaCasts')
            && isset($this->metaCasts[$key])
        ) {
            return $this->metaCasts[$key];
        }

        return null;
    }

    public function mergeMetaCasts(array $casts): void
    {
        $this->mergedMetaCasts = array_merge($this->mergedMetaCasts, $casts);
    }

    private function valueToString(mixed $value): string
    {
        return $this->getHandlerForValue($value)->serializeValue($value);
    }

    private function valueToNumeric(mixed $value): int|float
    {
        $numericValue = $this->getHandlerForValue($value)->getNumericValue($value);

        if ($numericValue === null) {
            throw new \InvalidArgumentException('Cannot convert to a numeric value');
        }

        return $numericValue;
    }

    private function getHandlerForValue(mixed $value): HandlerInterface
    {
        /** @var Registry $registry */
        $registry = app('metable.datatype.registry');
        return $registry->getHandlerForValue($value);
    }

    /**
     * @param Builder $q
     * @param string[] $stringValues
     * @return array{bool, bool} [needPartialMatch, needExactMatch]
     */
    protected function determineQueryValueMatchTypes(
        Builder $q,
        array $stringValues
    ): array {
        $driver = $q->getConnection()->getDriverName();
        $indexLength = (int)config('metable.stringValueIndexLength', 255);

        // only sqlite and pgsql support expression indexes, which must be partially matched
        // mysql and mariadb support prefix indexes, which works with the entire value
        // sqlserv does not support any substring indexing mechanism
        if (!in_array($driver, ['sqlite', 'pgsql'])) {
            return [false, true];
        }
        // if any value is longer than the index length, we need to do both a
        // substring match to leverage the index and an exact match to avoid false positives
        foreach ($stringValues as $stringValue) {
            if (strlen($stringValue) > $indexLength) {
                return [true, true];
            }
        }

        // if all values are shorter than the index length,
        // we only need to do a substring match
        return [true, false];
    }

    abstract public function getKey();

    abstract public function getMorphClass();

    abstract protected function castAttribute($key, $value);

    abstract public function morphMany($related, $name, $type = null, $id = null, $localKey = null);

    abstract public function load($relations);

    abstract public function relationLoaded($key);

    abstract protected function castAttributeAsHashedString($key, $value);

    /**
     * @param mixed $value
     * @param string $cast
     * @return Collection|\Illuminate\Support\Collection
     */
    protected function castMetaToCollection(string $cast, mixed $value): \Illuminate\Support\Collection
    {
        if ($value instanceof \Illuminate\Support\Collection) {
            $collection = $value;
        } elseif ($value instanceof Model) {
            $collection = $value->newCollection([$value]);
        } elseif (is_iterable($value)) {
            $isEloquentModels = true;
            $notEmpty = false;

            foreach ($value as $item) {
                $notEmpty = true;
                if (!$item instanceof Model) {
                    $isEloquentModels = false;
                    break;
                }
            }
            $collection = $isEloquentModels && $notEmpty
                ? $value[0]->newCollection($value)
                : collect($value);
        }

        if (str_starts_with($cast, 'collection:')) {
            $class = substr($cast, 11);
            $collection->each(function ($item) use ($class): void {
                if (!$item instanceof $class) {
                    throw CastException::invalidClassCast($class, $item);
                }
            });
        }

        return $collection;
    }

    /**
     * @param string $cast
     * @param mixed $value
     * @return mixed
     * @throws \JsonException
     */
    protected function castMetaToJson(string $cast, mixed $value): mixed
    {
        $assoc = $cast == 'array';
        if (is_string($value)) {
            $value = json_decode($value, $assoc, 512, JSON_THROW_ON_ERROR);
        }
        return json_decode(
            json_encode($value, JSON_THROW_ON_ERROR),
            $assoc,
            512,
            JSON_THROW_ON_ERROR
        );
    }

    /**
     * @param mixed $value
     * @param string $cast
     * @return mixed
     */
    protected function castMetaToClass(mixed $value, string $cast): mixed
    {
        if ($value instanceof $cast) {
            return $value;
        }

        if (is_a($cast, Model::class, true)
            && (is_string($value) || is_int($value))
        ) {
            return $cast::findOrFail($value);
        }

        throw CastException::invalidClassCast($cast, $value);
    }
}
