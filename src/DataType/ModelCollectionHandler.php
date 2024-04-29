<?php

namespace Plank\Metable\DataType;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Handle serialization of Eloquent collections.
 */
class ModelCollectionHandler implements HandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDataType(): string
    {
        return 'collection';
    }

    /**
     * {@inheritdoc}
     */
    public function canHandleValue(mixed $value): bool
    {
        return $value instanceof Collection;
    }

    /**
     * {@inheritdoc}
     */
    public function serializeValue(mixed $value): string
    {
        $items = [];
        foreach ($value as $key => $model) {
            $items[$key] = [
                'class' => get_class($model),
                'key' => $model->exists ? $model->getKey() : null,
            ];
        }

        return json_encode(['class' => get_class($value), 'items' => $items]);
    }

    /**
     * {@inheritdoc}
     */
    public function unserializeValue(string $serializedValue): mixed
    {
        $data = json_decode($serializedValue, true);

        $collectionClass = (string)($data['class'] ?? '');

        if (class_exists($collectionClass)
            && is_a($collectionClass, Collection::class, true)
        ) {
            $collection = new $collectionClass();
        } else {
            // attempt to gracefully fall back to a standard collection
            // if the defined collection class is not found
            $collection = new Collection();
        }

        $models = $this->loadModels($data['items']);


        // Repopulate collection keys with loaded models.
        foreach ($data['items'] as $key => $item) {
            if (empty($item['key'])) {
                $class = (string)($item['class'] ?? '');
                if (!class_exists($class)
                    || !is_a($class, Model::class, true)
                ) {
                    continue;
                }
                $collection[$key] = new $item['class']();
            } elseif (isset($models[$item['class']][$item['key']])) {
                $collection[$key] = $models[$item['class']][$item['key']];
            }
        }

        return $collection;
    }

    /**
     * Load each model instance, grouped by class.
     *
     * @param array $items
     *
     * @return array
     */
    private function loadModels(array $items)
    {
        $classes = [];
        $results = [];

        // Retrieve a list of keys to load from each class.
        foreach ($items as $item) {
            $class = (string)($item['class'] ?? '');

            if (!empty($item['key'])) {
                $classes[$class][] = $item['key'];
            }
        }

        // Iterate list of classes and load all records matching a key.
        foreach ($classes as $class => $keys) {
            if (!class_exists($class)
                || !is_a($class, Model::class, true)
            ) {
                continue;
            }

            $results[$class] = $class::query()->findMany($keys)
                ->keyBy(fn (Model $model) => $model->getKey());
        }

        return $results;
    }

    public function getNumericValue(mixed $value): null|int|float
    {
        return null;
    }

    public function useHmacVerification(): bool
    {
        return false;
    }
}
