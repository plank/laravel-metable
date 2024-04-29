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
     *
     * If you change this list, it may be necessary to refresh the meta table with the `artisan metable:refresh` command.
     */
    'datatypes' => [
        Plank\Metable\DataType\NullHandler::class,
        Plank\Metable\DataType\BooleanHandler::class,
        Plank\Metable\DataType\IntegerHandler::class,
        Plank\Metable\DataType\FloatHandler::class,
        Plank\Metable\DataType\StringHandler::class,
        Plank\Metable\DataType\StringableHandler::class,
        Plank\Metable\DataType\DateTimeImmutableHandler::class,
        Plank\Metable\DataType\DateTimeHandler::class,
        Plank\Metable\DataType\BackedEnumHandler::class,
        Plank\Metable\DataType\PureEnumHandler::class,
        Plank\Metable\DataType\ModelHandler::class,
        Plank\Metable\DataType\ModelCollectionHandler::class,

        /*
         * The following handler is a catch-all that will encode anything.
         * It should come after all other handlers in active use
         *
         * Any handlers listed after this one will only be used for unserializing existing meta
         */
        Plank\Metable\DataType\SignedSerializeHandler::class,

        /*
         * The following handlers are deprecated and will be removed in a future release.
         * They are kept for backwards compatibility, but should not be used in new code.
         */
         // Plank\Metable\DataType\ArrayHandler::class,
         // Plank\Metable\DataType\ObjectHandler::class,
         // Plank\Metable\DataType\SerializableHandler::class,
    ],

    /*
     * List of classes that are allowed to be unserialized by the SignedSerializeHandler.
     * If true, all classes are allowed. If false, no classes are allowed.
     * If an array, only classes listed in the array are allowed.
     *
     * SignedSerializeHandler employs hmac verification to prevent PHP object injection attacks,
     * so allowing all classes is generally safe.
     */
    'signedSerializeHandlerAllowedClasses' => true,

    /*
     * List of classes that are allowed to be unserialized by the deprecated SerializableHandler.
     * If true, all classes are allowed. If false, no classes are allowed.
     * If an array, only classes listed in the array are allowed.
     *
     * This is the only protection against PHP object injection attacks, so it is strongly
     * recommended to list allowed classes or set to false.
     */
    'serializableHandlerAllowedClasses' => [
        // \SampleClass::class,
    ],

    /**
     * Number of bytes of the to index for strings
     * This value is used to determine the length of the prefix index on the value column in the database.
     * Higher values allow for better precision when querying, but will use more disk space in the database.
     *
     * Prefix index is only supported on the 'mysql', 'mariadb', 'pgsql', and 'sqlite' database drivers.
     *
     * Set to 0 before running the migration to disable the index.
     */
    'stringValueIndexLength' => 255,
];
