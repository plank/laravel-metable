<?php

namespace Plank\Metable\DataType;

interface Handler
{
	public function canHandleValue($value) : bool;
	public function serializeValue($value) : string;
	public function unserializeValue(string $serializedValue);
}