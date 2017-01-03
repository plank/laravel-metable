<?php

namespace Plank\Metable\DataType;

use DateTimeInterface;
use Carbon\Carbon;

class DateTimeHandler implements Handler
{

	/**
	 * The date format to use for serializing.
	 * @var string
	 */
	protected $format = 'Y-m-d H:i:s.uO';

	/**
	 * {@InheritDoc}
	 */
	public function getDataType() : string
	{
		return 'datetime';
	}

	/**
	 * {@InheritDoc}
	 */
	public function canHandleValue($value) : bool
	{
		return $value instanceof DateTimeInterface;
	}

	/**
	 * {@InheritDoc}
	 */
	public function serializeValue($value) : string
	{
		return $value->format($this->format);
	}

	/**
	 * {@InheritDoc}
	 */
	public function unserializeValue(string $value)
	{
		return Carbon::createFromFormat($this->format, $value);
	}
}