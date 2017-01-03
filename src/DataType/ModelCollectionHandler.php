<?php

namespace Plank\Metable\DataType;

use Illuminate\Database\Eloquent\Collection;

class ModelCollectionHandler implements Handler
{

	public function getDataType() : string
	{
		return 'collection';
	}

	public function canHandleValue($value) : bool
	{
		return $value instanceof Collection;
	}

	public function serializeValue($value) : string
	{
		foreach ($value as $key => $model) {
			$items[$key] = [
				'class' => get_class($model), 
				'key' => $model->exists ? $model->getKey() : null
			];
		}
		return json_encode(['class'=> get_class($value), 'items' => $items]);
	}

	public function unserializeValue(string $value)
	{
		$map = json_decode($value, true);
		
		$collection = new $map['class'];
		$models = $this->loadModels($map['items']);

		foreach ($map['items'] as $key => $item) {
			if (is_null($item['key'])) {
				$collection[$key] = new $item['class'];
			}else{
				$collection[$key] = $models[$item['class']][$item['key']];
			}
		}	

		return $collection;
	}

	private function loadModels($items){
		$classes = [];
		$results = [];

		foreach ($items as $item) {
			if(!is_null($item['key'])){
				$classes[$item['class']][] = $item['key'];
			}
		}

		foreach ($classes as $class => $keys) {
			$model = new $class;
			$results[$class] = $model->whereIn($model->getKeyName(), $keys)->get()->keyBy($model->getKeyName());
		}

		return $results;
	}
}