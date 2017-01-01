<?php

namespace Plank\Metable\DataType;

use DateTimeInterface;
use Carbon\Carbon;

class DateTimeHandler implements Handler
{

	protected $format = 'Y-m-d H:i:s.uO';

	public function canHandleValue($value) : bool
	{
		return $value instanceof DateTimeInterface;
	}

	public function serializeValue($value) : string
	{
		return $value->format($this->format);
	}

	public function unserializeValue(string $value)
	{
		return Carbon::createFromFormat($this->format, $value);
	}
}