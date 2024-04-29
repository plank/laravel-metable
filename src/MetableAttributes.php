<?php

namespace Plank\Metable;

use Illuminate\Support\Collection;

trait MetableAttributes
{
    public function getAttribute($key)
    {
        if ($this->isMetaAttribute($key)) {
            return $this->getMeta($this->metaAttributeToKey($key));
        }

        return parent::getAttribute($key);
    }

    public function setAttribute($key, $value)
    {
        if ($this->isMetaAttribute($key)) {
            $this->setMeta($this->metaAttributeToKey($key), $value);
            return;
        }

        parent::setAttribute($key, $value);
    }

    public function fill(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            if ($this->isMetaAttribute($key)) {
                $this->setMeta($this->metaAttributeToKey($key), $value);
                unset($attributes[$key]);
            }
        }

        parent::fill($attributes);
    }

    public function getMetaAttributes(): Collection
    {
        $attributes = [];
        foreach ($this->getAllMeta() as $key => $value) {
            $attributes[$this->metaKeyToAttribute($key)] = $value;
        }
        return collect($attributes);
    }

    public function offsetExists($key): bool
    {
        if ($this->isMetaAttribute($key)) {
            return $this->hasMeta($this->metaAttributeToKey($key));
        }

        return parent::offsetExists($key);
    }

    public function offsetUnset($key): void
    {
        if ($this->isMetaAttribute($key)) {
            $this->removeMeta($this->metaAttributeToKey($key));
            return;
        }

        parent::offsetUnset($key);
    }

    protected function isMetaAttribute($key): bool
    {
        return str_starts_with($key, 'meta_')
            && !array_key_exists($key, $this->attributes)
            && !array_key_exists($key, $this->casts);
    }

    public function toArray()
    {
        if (property_exists($this, 'includeMetaInArray')
            && !$this->includeMetaInArray
        ) {
            return parent::toArray();
        }

        return array_merge(
            parent::toArray(),
            $this->getArrayableItems($this->getMetaAttributes()->toArray())
        );
    }

    public function makeMetaHidden(): void
    {
        $this->hidden = array_merge(
            $this->hidden,
            array_keys($this->getMetaAttributes()->toArray())
        );
    }

    protected function metaAttributeToKey(string $attribute): string
    {
        return substr($attribute, 5);
    }

    protected function metaKeyToAttribute(string $key): string
    {
        return 'meta_' . $key;
    }

    abstract public function getMeta(string $key, mixed $default = null): mixed;

    abstract public function setMeta(string $key, mixed $value, bool $encrypt = false): void;

    abstract public function removeMeta(string $key): void;

    abstract public function getAllMeta(): \Illuminate\Support\Collection;

    abstract protected function getArrayableItems(array $values);
}
