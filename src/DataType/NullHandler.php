<?php

namespace Plank\Metable\DataType;

class NullHandler extends PrimitiveHandler
{
    protected $type = 'NULL';

    public function getDataType() : string
	{
		return 'null';
	}
}
