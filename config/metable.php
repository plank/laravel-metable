<?php

return [
	/*
	 * List of handlers for recognized data types
	 *
	 * Handlers will be evaluated in order, so a value will be handled by the first appropriate handler in the list.
	 */
	'datatypes' => [
		'boolean' => Plank\Metable\DataType\BooleanHandler::class,
		'null' => Plank\Metable\DataType\NullHandler::class,
		'integer' => Plank\Metable\DataType\IntegerHandler::class,
		'double' => Plank\Metable\DataType\DoubleHandler::class,
		'string' => Plank\Metable\DataType\StringHandler::class,
		'datetime' => Plank\Metable\DataType\DateTimeHandler::class,
		'model' => Plank\Metable\DataType\ModelHandler::class,
		'collection' => Plank\Metable\DataType\ModelCollectionHandler::class,
		'array' => Plank\Metable\DataType\ArrayHandler::class,
		'object' => Plank\Metable\DataType\ObjectHandler::class,
	]
];