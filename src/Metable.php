<?php

namespace Plank\Metable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Metable
{
	public function meta() : MorphMany
	{
		return $this->morphMany(Meta::class, 'metable');
	}

	public function setMeta(string $key, $value)
    {
        $key = strtolower($key);

        if ($this->hasMeta($key)) {
            $meta = $this->getMetaRecord($key);
            $meta->value = $value;
            $meta->save();
        } else {
            $meta = $this->makeMeta($key, $value);
            $this->meta()->save($meta);
        }

        //update cached relationship, if necessary
        if ($this->relationLoaded('meta')) {
        	$this->meta[$key] = $meta;
        }
    }

    public function syncMeta($array)
    {
        $meta = [];

        foreach ($array as $key => $value) {
            $meta[] = $value instanceof Meta ? $value : $this->makeMeta($key, $value);
        }

        $this->meta()->delete();
        $this->meta()->saveMany($meta);
    }

    public function getMeta(string $key, $default = null)
    {
        if( $this->hasMeta($key)) {
            return $this->getMetaRecord($key)->getAttribute('value');
        }
        return $default;
    }

    public function hasMeta(string $key) : bool
    {
        return $this->meta->has($key);
    }

    public function removeMeta($key)
    {
        $this->getMetaRecord($key)->delete();
        $this->meta->forget($key);
    }


    public function getMetaRecord(string $key)
    {
        return $this->meta->get($key);
    }

    public function scopeWhereHasMeta(Builder $q, $key)
    {
        $q->whereHas('meta', function(Builder $q) use($key){
            $q->whereIn('key', (array) $key);
        });
    }

    public function scopeWhereHasMetaKeys(Builder $q, array $keys)
    {
        $q->whereHas('meta', function(Builder $q) use($keys){
            $q->whereIn('key', $keys);
        }, '=', count($keys));
    }

    public function scopeWhereMeta(Builder $q, string $key, $operator, $value = null)
    {
        // shift arguments if no operator present
        if (!isset($value)) {
            $value = $operator;
            $operator = '=';
        }

        // Perform numeric comparison on numbers (slow)
        if (is_numeric($value) && in_array($operator, ['<', '<=', '>', '>='])) {
            return $q->whereMetaNumeric($key, $operator, $value);
        }

        // convert value to its serialized version for comparison
        if (!is_string($value)) {
            $value = $this->makeMeta($key, $value)->getRawValue();
        }

        $q->whereHas('meta', function(Builder $q) use($key, $operator, $value){
            $q->where('key', $key);
            $q->where('value', $operator, $value);
        });
    }

    public function scopeWhereMetaNumeric(Builder $q, string $key, $operator, $value)
    {
        $q->whereHas('meta', function(Builder $q) use($key, $operator, $value){
            $q->where('key', $key);
            $q->whereRaw("cast(value as numeric) {$operator} ?", [(float)$value]);
        });
    }

    public function scopeWhereMetaIn(Builder $q, string $key, array $value)
    {
        $value = array_map(function($val){
            return is_string($val) ? $val :$this->makeMeta($key, $value)->getRawValue();
        }, $value);
        $q->whereHas('meta', function(Builder $q) use($key, $value){
            $q->where('key', $key);
            $q->whereIn('value', $value);
        });
    }


    /**
     * {@InheritDoc}
     */
    public function setRelation($relation, $value)
    {
        if ($relation == 'meta') {
            // keep the meta relation indexed by key
            $value = $value->keyBy('key');
        }
        return parent::setRelation($relation, $value);
    }


    private function makeMeta(string $key, $value) : Meta
    {
        $meta = new Meta([
            'key' => $key,
            'value' => $value
        ]);
        return $meta;
    }
    
}