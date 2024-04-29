.. _datatypes:

Data Types
===========================================

.. highlight:: php

You can attach a number of different kinds of values to a ``Metable`` model. The data types that are supported by Laravel-Mediable out of the box are the following.

Meta encoded with different data types support different query scopes for filtering by meta value. See :ref:`querying_meta` for more information on available query scopes.

Scalar Values
---------------

The following scalar values are supported.

Boolean
^^^^^^^^
+----------------------+-----+
| Handler              | ``\Plank\Metable\DataType\BooleanHandler`` |
| String Query Scopes  | Yes |
| Numeric Query Scopes | Yes |
| Other Query Scopes   |     |
+----------------------+-----+

::

    <?php
    $metable->setMeta('accepted_promotion', true);

Integer
^^^^^^^^
+----------------------+-----+
| Handler              | ``\Plank\Metable\DataType\IntegerHandler`` |
| String Query Scopes  | Yes |
| Numeric Query Scopes | Yes |
| Other Query Scopes   |     |
+----------------------+-----+

::

    <?php
    $metable->setMeta('likes', 9001);

Float
^^^^^^^^
+----------------------+-----+
| Handler              | ``\Plank\Metable\DataType\FloatHandler`` |
| String Query Scopes  | Yes |
| Numeric Query Scopes | Yes |
| Other Query Scopes   |     |
+----------------------+-----+

::

    <?php
    $metable->setMeta('precision', 0.755);

Null
^^^^^^^^
+----------------------+-----+
| Handler              | ``\Plank\Metable\DataType\NullHandler`` |
| String Query Scopes  | Yes |
| Numeric Query Scopes | No |
| Other Query Scopes   | whereMetaIsNull() |
+----------------------+-----+

::

    <?php
    $metable->setMeta('linked_model', null);

String
^^^^^^^^
+----------------------+-----+
| Handler              | ``\Plank\Metable\DataType\StringHandler`` |
| String Query Scopes  | Yes |
| Numeric Query Scopes | if string is numeric |
| Other Query Scopes   |     |
+----------------------+-----+

::

    <?php
    $metable->setMeta('attachment', '/var/www/html/public/attachment.pdf');

Composite Values
----------------

The following classes and interfaces are supported.

.. _eloquent_models:

Eloquent Models
^^^^^^^^^^^^^^^^^

+----------------------+-----+
| Handler              | ``\Plank\Metable\DataType\ModelHandler`` |
| String Query Scopes  | Yes |
| Numeric Query Scopes | No  |
| Other Query Scopes   | whereMetaIsModel() |
+----------------------+-----+

It is possible to attach another Eloquent model to a ``Metable`` model.

::

    <?php
    $page = App\Page::where(['title' => 'Welcome'])->first();
    $metable->setMeta('linked_model', $page);

When ``$metable->getMeta()`` is called, the attached model will be reloaded from the database.

It is also possible to attach a ``Model`` instance that has not been saved to the database.

::

    <?php
    $metable->setMeta('related', new App\Page);

When ``$metable->getMeta()`` is called, a fresh instance of the class will be created (will not include any attributes).

 
Eloquent Collections
^^^^^^^^^^^^^^^^^^^^

+----------------------+-----+
| Handler              | ``\Plank\Metable\DataType\ModelCollectionHandler`` |
| String Query Scopes  | Yes |
| Numeric Query Scopes | No  |
| Other Query Scopes   |     |
+----------------------+-----+

Similarly, it is possible to attach multiple models to a key by providing an instance of ``Illuminate\Database\Eloquent\Collection`` containing the models. 

As with individual models, both existing and unsaved instances can be stored.

::

    <?php
    $users = App\User::where(['title' => 'developer'])->get();
    $metable->setMeta('authorized', $users);

DateTime & Carbon
^^^^^^^^^^^^^^^^^^
+----------------------+-----+
| Handler              | ``\Plank\Metable\DataType\DateTimeHandler`` |
| String Query Scopes  | Yes |
| Numeric Query Scopes | Yes (timestamp) |
| Other Query Scopes   |     |
+----------------------+-----+

Any object implementing the ``DateTimeInterface``.  Object will be converted to a ``Carbon`` instance when unserialized.

::

    <?php
    $metable->setMeta('last_viewed', \Carbon\Carbon::now());

DateTimeImmutable & CarbonImmutable
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

+----------------------+-----+
| Handler              | ``\Plank\Metable\DataType\DateTimeImmutableHandler`` |
| String Query Scopes  | Yes |
| Numeric Query Scopes | Yes (timestamp) |
| Other Query Scopes   |     |
+----------------------+-----+

Any object extending the ``DateTimeImmutable`` class.  Object will be converted to a ``CarbonImmutable`` instance when unserialized.

::

    <?php
    $metable->setMeta('completed_at', \Carbon\CarbonImmutable::now());

Stringable
^^^^^^^^^^
+----------------------+-----+
| Handler              | ``\Plank\Metable\DataType\StringableHandler`` |
| String Query Scopes  | Yes |
| Numeric Query Scopes | If numeric string |
| Other Query Scopes   |     |
+----------------------+-----+

Strings wrapped in Laravel's ``Illuminate\Support\Stringable`` fluent interface.

::

    <?php
    $metable->setMeta('address', Str::of('123 Somewhere St.'));

Enums
^^^^^^^^
+----------------------+-----+
| Handler              | ``\Plank\Metable\DataType\PureEnumHandler``<br />``\Plank\Metable\DataType\BackedEnumHandler`` |
| String Query Scopes  | Yes |
| Numeric Query Scopes | If backed with integer or numeric-string |
| Other Query Scopes   |     |
+----------------------+-----+

::

    <?php
    $metable->setMeta('status', Status::ACTIVE);

Objects and Arrays
^^^^^

+----------------------+-----+
| Handler              | ``\Plank\Metable\DataType\SignedSerializeHandler`` |
| String Query Scopes  | Yes |
| Numeric Query Scopes | No  |
| Other Query Scopes   |     |
+----------------------+-----+

Objects and arrays will be serialized using PHP's ``serialize()`` function, to allow for the storage and retrieval of complex data structures.

::

    <?php
    $metable->setMeta('data', ['key' => 'value']);
    $metable->setMeta('data', new MyValueObject(123));

The serialized value is cryptographically signed with an HMAC which is verified before the data is unserialized to prevent PHP object injection attacks. The application's ``APP_KEY`` is used as the HMAC signing key. HMAC verification is generally sufficient for preventing PHP object injection attacks, but it possible to further restrict what can be unserialized by specifying an array or class name in the ``metable.SignedSerializeHandlerAllowedClasses`` config in the ``config/metable.php`` file.

.. note:: The ``Plank\Metable\DataType\SignedSerializeHandler`` class should generally be the last entry the ``config/metable.php`` datatypes array, as it will accept data of any type, causing any handlers below it to be ignored for serializing new meta values. Any handlers defined below it will still be used for unserializing existing meta values. This can be used to temporarily provide backwards compatibility for deprecated data types.

Deprecated
----------

The following data types are deprecated and should not be used in new code. They are still supported for backwards compatibility, but will be removed in a future release.

Array
^^^^^^^^

+----------------------+-----+
| Handler              | ``\Plank\Metable\DataType\ArrayHandler`` |
| String Query Scopes  | Yes |
| Numeric Query Scopes | No  |
| Other Query Scopes   |     |
+----------------------+-----+

.. warning:: The ``ArrayHandler`` datatype is deprecated. The ``SignedSerializeHandler`` should be used for handling arrays.

Arrays of scalar values. Nested arrays are supported.

::

    <?php
    $metable->setMeta('information', [
        'address' => [
            'street' => '123 Somewhere Ave.',
            'city' => 'Somewhereville',
            'country' => 'Somewhereland',
            'postal' => '123456',
        ],
        'contact' => [
            'phone' => '555-555-5555',
            'email' => 'email@example.com'
        ]
    ]);

.. warning:: the ``ArrayHandler`` class uses ``json_encode()`` and ``json_decode()`` under the hood for array serialization. This will cause any objects nested within the array to be cast to an array. This is not a concern for the ``SignedSerializeHandler``.

Serializable
^^^^^^^^^^^^^

+----------------------+-----+
| Handler              | ``\Plank\Metable\DataType\ArrayHandler`` |
| String Query Scopes  | Yes |
| Numeric Query Scopes | No  |
| Other Query Scopes   |     |
+----------------------+-----+

.. warning:: The ``SerializableHandler`` datatype is deprecated. The ``SignedSerializeHandler`` should be used for handling all objects.

Any object implementing the PHP ``Serializable`` interface.

::

    <?php
    class Example implements \Serializable
    {
        //...
    }

    $serializable = new Example;

    $metable->setMeta('example', $serializable);

For security reasons, it is necessary to list any classes that can be unserialized in the ``metable.serializableHandlerAllowedClasses`` key in the ``config/metable.php`` file. This is to prevent PHP Object Injection vulnerabilities when unserializing untrusted data. This config can be set to true to allow all classes, but this is not recommended.

Plain Objects
^^^^^^^^^^^^^^

+----------------------+-----+
| Handler              | ``\Plank\Metable\DataType\ArrayHandler`` |
| String Query Scopes  | Yes |
| Numeric Query Scopes | No  |
| Other Query Scopes   |     |
+----------------------+-----+

.. warning:: The ``ObjectHandler`` datatype is deprecated. The ``SignedSerializeHandler`` should be used for handling all objects.

Any other objects will be converted to ``stdClass`` plain objects. You can control what properties are stored by implementing the ``JsonSerializable`` interface on the class of your stored object.

::

    <?php
    $metable->setMeta('weight', new Weight(10, 'kg'));
    $weight = $metable->getMeta('weight') // stdClass($amount = 10; $unit => 'kg');

.. warning:: ``ObjectHandler`` class uses ``json_encode()`` and ``json_decode()`` under the hood for plain object serialization. This will cause any arrays within the object's properties to be cast to a ``stdClass`` object. This is not a concern for the ``SignedSerializeHandler``.


Adding Custom Data Types
------------------------

You can add support for other data types by creating a new ``Handler`` for your class, which can take care of serialization. Only objects which can be converted to a string and then rebuilt from that string should be handled. 


Define a class which implements the `Plank\\Metable\\DataType\\Handler <https://github.com/plank/laravel-metable/blob/master/src/DataType/Handler.php>`_ interface and register it to the ``'datatypes'`` array in ``config/metable.php``. The order of the handlers in the array is important, as Laravel-Metable will iterate through them and use the first entry that returns ``true`` for the ``canHandleValue()`` method for a given value. Make sure more concrete classes come before more abstract ones.
