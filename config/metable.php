<?php

return [
    /*
     * Model class to use for Meta.
     */
    'model' => Plank\Metable\Meta::class,

    /**
     * Whether to apply migrations from this package automatically.
     */
    'applyMigrations' => true,

    /*
     * List of handlers for recognized data types.
     *
     * Handlers will be evaluated in order, so a value will be handled
     * by the first appropriate handler in the list.
     */
    'datatypes' => [
        Plank\Metable\DataType\BooleanHandler::class,
        Plank\Metable\DataType\NullHandler::class,
        Plank\Metable\DataType\IntegerHandler::class,
        Plank\Metable\DataType\FloatHandler::class,
        Plank\Metable\DataType\StringHandler::class,
        Plank\Metable\DataType\DateTimeHandler::class,
        Plank\Metable\DataType\ModelHandler::class,
        Plank\Metable\DataType\ModelCollectionHandler::class,

        /*
         * The following handlers are catch-all handlers that will encode anything.
         * Only one of these should be enabled at a time.
         */
        Plank\Metable\DataType\SerializeHandler::class,
        // Plank\Metable\DataType\JsonHandler::class,

        /*
         * The following handlers are deprecated and will be removed in a future release.
         * They are kept for backwards compatibility, but should not be used in new code.
         */
         // Plank\Metable\DataType\ArrayHandler::class,
         // Plank\Metable\DataType\ObjectHandler::class,
         // Plank\Metable\DataType\SerializableHandler::class,
    ],

    'options' => [
        'serializable' => [
            /*
             * List of classes that may be stored and retrieved using PHP serialization.
             *
             * Must explicitly list all classes that may be unserialized.
             * Child classes of listed classes are not allowed, unless they are listed.
             *
             * May be set to an empty array or `false` to disallow object unserialization.
             * May be set to `true` to allow serialization of all classes (strongly discouraged).
             */
            'allowedClasses' => [
                // \SampleClass::class,
            ],
        ],
    ],

    /**
     * Whether to index complex data types (arrays, objects, etc).
     * If enabled the value will be serialized and the first 255 characters will be indexed.
     * This allows for using whereMeta*() query scopes on serialized values, but may have
     * performance and disk usage implications for large data sets.
     *
     * If you do not intend to query meta values containing complex data types, you should leave this disabled.
     */
    'indexComplexDataTypes' => false,

    /**
     * Number of bytes to index for strings and complex data types.
     * This value is used to determine the length of the index column in the database.
     * Higher values allow for better precision when querying,
     * but will use more disk space in the database.
     */
    'stringValueIndexLength' => 255,
];
