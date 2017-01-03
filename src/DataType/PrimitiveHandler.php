<?php

namespace Plank\Metable\DataType;

abstract class PrimitiveHandler implements Handler
{
	protected $type;

	public function getDataType() : string
	{
		return $this->type;
	}

	public function canHandleValue($value) : bool
	{
		return gettype($value) == $this->type;
	}

	public function serializeValue($value) : string
	{
		settype($value, 'string');
		return $value;
	}

	public function unserializeValue(string $value)
	{
		settype($value, $this->type);
		return $value;
	}
}