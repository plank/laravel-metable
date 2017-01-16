<?php

namespace Plank\Metable\DataType;

/**
 * Handle serialization of booleans.
 *
 * @author Sean Fraser <sean@plankdesign.com>
 */
class BooleanHandler extends ScalarHandler
{
    /**
     * {@inheritdoc}
     */
    protected $type = 'boolean';
}
