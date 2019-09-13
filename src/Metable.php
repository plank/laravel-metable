<?php

namespace Plank\Metable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Query\JoinClause;
use Traversable;

/**
 * Trait for giving Eloquent models the ability to handle Meta.
 *
 * @property Collection|Meta[] $meta
 * @method static Builder whereHasMeta($key): void
 * @method static Builder WhereDoesntHaveMeta($key)
 * @method static Builder WhereHasMetaKeys(array $keys)
 * @method static Builder WhereMeta(string $key, $operator, $value = null)
 * @method static Builder WhereMetaNumeric(string $key, string $operator, $value)
 * @method static Builder WhereMetaIn(string $key, array $values)
 * @method static Builder OrderByMeta(string $key, string $direction = 'asc', $strict = false)
 * @method static Builder OrderByMetaNumeric(string $key, string $direction = 'asc', $strict = false)
 */
trait Metable
{
    /**
     * Initialize the trait.
     *
     * @return void
     */
    public static function bootMetable()
    {
        // delete all attached meta on deletion
        static::deleted(function (self $model) {
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
    public function setMeta(string $key, $value): void
    {
        if ($this->hasMeta($key)) {
            $meta = $this->getMetaRecord($key);
            $meta->setAttribute('value', $value);
            $meta->save();
        } else {
            $meta = $this->makeMeta($key, $value);
            $this->meta()->save($meta);
        }

        // Update cached relationship, if necessary.
        if ($this->relationLoaded('meta')) {
            $this->meta[$key] = $meta;
        }
    }

    /**
     * Replace all associated `Meta` with the keys and values provided.
     *
     * @param array|Traversable $array
     *
     * @return void
     */
    public function syncMeta($array): void
    {
        $meta = [];

        foreach ($array as $key => $value) {
            $meta[$key] = $this->makeMeta($key, $value);
        }

        $this->meta()->delete();
        $this->meta()->saveMany($meta);

        // Update cached relationship.
        $collection = $this->makeMeta()->newCollection($meta);
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
    public function getMeta(string $key, $default = null)
    {
        if ($this->hasMeta($key)) {
            return $this->getMetaRecord($key)->getAttribute('value');
        }

        return $default;
    }

    /**
     * Retrieve all meta attached to the model as a key/value map.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllMeta(): \Illuminate\Support\Collection
    {
        return $this->getMetaCollection()->toBase()->map(function (Meta $meta) {
            return $meta->getAttribute('value');
        });
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
        $this->getMetaCollection()->pull($key)->delete();
    }

    /**
     * Delete all meta attached to the model.
     *
     * @return void
     */
    public function purgeMeta(): void
    {
        $this->meta()->delete();
        $this->setRelation('meta', $this->makeMeta()->newCollection([]));
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
     * @param string|array $key
     *
     * @return void
     */
    public function scopeWhereHasMeta(Builder $q, $key): void
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
     * @param string|array $key
     *
     * @return void
     */
    public function scopeWhereDoesntHaveMeta(Builder $q, $key): void
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
    public function scopeWhereMeta(Builder $q, string $key, $operator, $value = null): void
    {
        // Shift arguments if no operator is present.
        if (!isset($value)) {
            $value = $operator;
            $operator = '=';
        }

        // Convert value to its serialized version for comparison.
        if (!is_string($value)) {
            $value = $this->makeMeta($key, $value)->getRawValue();
        }

        $q->whereHas('meta', function (Builder $q) use ($key, $operator, $value) {
            $q->where('key', $key);
            $q->where('value', $operator, $value);
        });
    }

    /**
     * Query scope to restrict the query to records which have `Meta` with a specific key and numeric value.
     *
     * Performs numeric comparison instead of string comparison.
     *
     * @param Builder $q
     * @param string $key
     * @param string $operator
     * @param int|float $value
     *
     * @return void
     */
    public function scopeWhereMetaNumeric(Builder $q, string $key, string $operator, $value): void
    {
        // Since we are manually interpolating into the query,
        // escape the operator to protect against injection.
        $validOperators = ['<', '<=', '>', '>=', '=', '<>', '!='];
        $operator = in_array($operator, $validOperators) ? $operator : '=';
        $field = $q->getQuery()
            ->getGrammar()
            ->wrap($this->meta()->getRelated()->getTable() . '.value');

        $q->whereHas('meta', function (Builder $q) use ($key, $operator, $value, $field) {
            $q->where('key', $key);
            $q->whereRaw("cast({$field} as decimal) {$operator} ?", [(float)$value]);
        });
    }

    /**
     * Query scope to restrict the query to records which have `Meta` with a specific key and a value within a specified set of options.
     *
     * @param Builder $q
     * @param string $key
     * @param array $values
     *
     * @return void
     */
    public function scopeWhereMetaIn(Builder $q, string $key, array $values): void
    {
        $values = array_map(function ($val) use ($key) {
            return is_string($val) ? $val : $this->makeMeta($key, $val)->getRawValue();
        }, $values);

        $q->whereHas('meta', function (Builder $q) use ($key, $values) {
            $q->where('key', $key);
            $q->whereIn('value', $values);
        });
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
        $strict = false
    ): void {
        $table = $this->joinMetaTable($q, $key, $strict ? 'inner' : 'left');
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
        $strict = false
    ): void {
        $table = $this->joinMetaTable($q, $key, $strict ? 'inner' : 'left');
        $direction = strtolower($direction) == 'asc' ? 'asc' : 'desc';
        $field = $q->getQuery()->getGrammar()->wrap("{$table}.value");

        $q->orderByRaw("cast({$field} as decimal) $direction");
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
    private function joinMetaTable(Builder $q, string $key, $type = 'left'): string
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
        $q->join("{$metaTable} as {$alias}", function (JoinClause $q) use ($relation, $key, $alias) {
            $q->on($relation->getQualifiedParentKeyName(), '=', $alias . '.' . $relation->getForeignKeyName())
                ->where($alias . '.key', '=', $key)
                ->where($alias . '.' . $relation->getMorphType(), '=', get_class($this));
        }, null, null, $type);

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
    private function getMetaCollection()
    {
        if (!$this->relationLoaded('meta')) {
            $this->setRelation('meta', $this->meta()->get());
        }

        return $this->getRelation('meta');
    }

    /**
     * {@inheritdoc}
     */
    public function setRelation($relation, $value)
    {
        if ($relation == 'meta') {
            // keep the meta relation indexed by key.
            /** @var Collection $value */
            $value = $value->keyBy('key');
        }

        return parent::setRelation($relation, $value);
    }

    /**
     * Retrieve the FQCN of the class to use for Meta models.
     *
     * @return string
     */
    protected function getMetaClassName(): string
    {
        return config('metable.model', Meta::class);
    }

    /**
     * Create a new `Meta` record.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return Meta
     */
    protected function makeMeta(string $key = '', $value = ''): Meta
    {
        $className = $this->getMetaClassName();

        $meta = new $className([
            'key' => $key,
            'value' => $value,
        ]);

        return $meta;
    }
}
