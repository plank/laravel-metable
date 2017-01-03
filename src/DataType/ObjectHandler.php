<?php

namespace Plank\Metable\DataType;

class ObjectHandler implements Handler
{

	public function getDataType() : string
	{
		return 'object';
	}

	public function canHandleValue($value) : bool
	{
		return is_object($value);
	}

	public function serializeValue($value) : string
	{
		return json_encode($value);
	}

	public function unserializeValue(string $value)
	{
		return json_decode($value, false);
	}
}