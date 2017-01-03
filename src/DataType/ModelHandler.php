<?php

namespace Plank\Metable\DataType;

use Illuminate\Database\Eloquent\Model;

class ModelHandler implements Handler
{
	/**
	 * {@InheritDoc}
	 */
	public function getDataType() : string
	{
		return 'model';
	}

	/**
	 * {@InheritDoc}
	 */
	public function canHandleValue($value) : bool
	{
		return $value instanceof Model;
	}

	/**
	 * {@InheritDoc}
	 */
	public function serializeValue($value) : string
	{
		if ($value->exists) {
			return get_class($value) . '#' . $value->getKey();
		}else{
			return get_class($value);
		}
	}

	/**
	 * {@InheritDoc}
	 */
	public function unserializeValue(string $value)
	{
		if(strpos($value, '#') === false) {
			return new $value;
		}

		list($class, $id) = explode('#', $value);
		return (new $class)->findOrFail($id);
	}
}