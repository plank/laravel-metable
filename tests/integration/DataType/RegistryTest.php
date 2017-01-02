<?php

use Plank\Metable\DataType\Registry;
use Plank\Metable\DataType\Handler;
use Plank\Metable\Exceptions\DataTypeException;

class RegistryTest extends TestCase
{
	public function test_it_can_set_a_handler()
	{
		$registry = new Registry;
		$handler = $this->getMock(Handler::class);
		$this->assertFalse($registry->hasHandlerForType('foo'));
		
		$registry->setHandlerForType($handler, 'foo');

		$this->assertTrue($registry->hasHandlerForType('foo'));
		$this->assertEquals($handler, $registry->getHandlerForType('foo'));
	}

	public function test_it_can_remove_a_handler()
	{
		$registry = new Registry;
		$handler = $this->getMock(Handler::class);
		$registry->setHandlerForType($handler, 'foo');
		$this->assertTrue($registry->hasHandlerForType('foo'));
		
		$registry->removeHandlerForType('foo');

		$this->assertFalse($registry->hasHandlerForType('foo'));
	}

	public function test_it_throws_an_exception_if_no_handler_set()
	{
		$registry = new Registry;

		$this->setExpectedException(DataTypeException::class);
		$registry->getHandlerForType('foo');
	}

	public function test_it_determines_best_handler_for_a_value()
	{
		$stringHandler = $this->getMock(Handler::class);
		$stringHandler->method('canHandleValue')
			->will($this->returnCallback(function($value){
			return is_string($value);
		}));
		$integerHandler = $this->getMock(Handler::class);
		$integerHandler->method('canHandleValue')
			->will($this->returnCallback(function($value){
			return is_integer($value);
		}));
		$registry = new Registry;
		$registry->setHandlerForType($stringHandler, 'str');
		$registry->setHandlerForType($integerHandler, 'int');

		$type1 = $registry->getTypeForValue(123);
		$type2 = $registry->getTypeForValue('abc');

		$this->assertEquals('int', $type1);
		$this->assertEquals('str', $type2);
	}

	public function test_it_throws_an_exception_if_no_type_matches_value()
	{
		$registry = new Registry;

		$this->setExpectedException(DataTypeException::class);

		$registry->getTypeForValue([]);
	}
}