<?php

namespace Plank\Metable\DataType;

use Plank\Metable\Exceptions\DataTypeException;

/**
 * List of available data type Handlers.
 */
class Registry
{
    /**
     * List of registered handlers .
     *
     * @var array
     */
    protected $handlers = [];

    /**
     * Append a Handler to use for a given type identifier.
     *
     * @param HandlerInterface $handler
     * @param string $type
     *
     * @return void
     */
    public function addHandler(HandlerInterface $handler)
    {
        $this->handlers[$handler->getDataType()] = $handler;
    }

    /**
     * Retrieve the handler assigned to a given type identifier.
     *
     * @param string $type
     *
     * @throws DataTypeException if no handler is found.
     *
     * @return HandlerInterface
     */
    public function getHandlerForType(string $type): HandlerInterface
    {
        if ($this->hasHandlerForType($type)) {
            return $this->handlers[$type];
        }

        throw DataTypeException::handlerNotFound($type);
    }

    /**
     * Check if a handler has been set for a given type identifier.
     *
     * @param string $type
     *
     * @return bool
     */
    public function hasHandlerForType(string $type): bool
    {
        return array_key_exists($type, $this->handlers);
    }

    /**
     * Removes the handler with a given type identifier.
     *
     * @param string $type
     *
     * @return void
     */
    public function removeHandlerForType(string $type)
    {
        unset($this->handlers[$type]);
    }

    /**
     * Find a data type Handler that is able to operate on the value, return the type identifier associated with it.
     *
     * @param mixed $value
     *
     * @throws DataTypeException if no handler can handle the value.
     *
     * @return string
     */
    public function getTypeForValue($value): string
    {
        foreach ($this->handlers as $type => $handler) {
            if ($handler->canHandleValue($value)) {
                return $type;
            }
        }

        throw DataTypeException::handlerNotFoundForValue($value);
    }
}
