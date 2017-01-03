<?php

namespace Plank\Metable\DataType;

class ArrayHandler implements Handler
{

	public function getDataType() : string
	{
		return 'array';
	}

	public function canHandleValue($value) : bool
	{
		return is_array($value);
	}

	public function serializeValue($value) : string
	{
		return json_encode($value);
	}

	public function unserializeValue(string $value)
	{
		return json_decode($value, true);
	}
}