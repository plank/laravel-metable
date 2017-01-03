<?php

namespace Plank\Metable\DataType;

class NullHandler extends ScalarHandler
{
    /**
	 * {@InheritDoc}
	 */
    protected $type = 'NULL';

	/**
	 * {@InheritDoc}
	 */
    public function getDataType() : string
	{
		return 'null';
	}
}
