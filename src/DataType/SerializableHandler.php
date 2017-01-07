<?php

namespace Plank\Metable\DataType;

use Serializable;

class SerializableHandler implements Handler
{
	/**
	 * {@InheritDoc}
	 */
	public function getDataType() : string
	{
		return 'serializable';
	}

	/**
	 * {@InheritDoc}
	 */
	public function canHandleValue($value) : bool
	{
		return $value instanceof Serializable;
	}

	/**
	 * {@InheritDoc}
	 */
	public function serializeValue($value) : string
	{
		return serialize($value);
	}

	/**
	 * {@InheritDoc}
	 */
	public function unserializeValue(string $value)
	{
		return unserialize($value);
	}
}