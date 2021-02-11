.. _datatypes:

Data Types
===========================================

.. highlight:: php

You can attach a number of different kinds of values to a ``Metable`` model. The data types that are supported by Laravel-Mediable out of the box are the following.

Scalar Values
---------------

The following scalar values are supported.

Array
^^^^^^^^

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

.. warning:: Laravel-Metable uses ``json_encode()`` and ``json_decode()`` under the hood for array serialization. This will cause any objects nested within the array to be cast to an array.

Boolean
^^^^^^^^

::

    <?php
    $metable->setMeta('accepted_promotion', true);

Integer
^^^^^^^^

::

    <?php
    $metable->setMeta('likes', 9001);

Float
^^^^^^^^

::

    <?php
    $metable->setMeta('precision', 0.755);

Null
^^^^^^^^

::

    <?php
    $metable->setMeta('linked_model', null);

String
^^^^^^^^

::

    <?php
    $metable->setMeta('attachment', '/var/www/html/public/attachment.pdf');

Objects
---------------

The following classes and interfaces are supported.

.. _eloquent_models:

Eloquent Models
^^^^^^^^^^^^^^^^^

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

Similarly, it is possible to attach multiple models to a key by providing an instance of ``Illuminate\Database\Eloquent\Collection`` containing the models. 

As with individual models, both existing and unsaved instances can be stored.

::

    <?php
    $users = App\User::where(['title' => 'developer'])->get();
    $metable->setMeta('authorized', $users);

DateTime & Carbon
^^^^^^^^^^^^^^^^^^

Any object implementing the ``DateTimeInterface``.  Object will be converted to a ``Carbon`` instance.

::

    <?php
    $metable->setMeta('last_viewed', \Carbon\Carbon::now());


Serializable
^^^^^^^^^^^^^

Any object implementing the PHP ``Serializable`` interface.

::

    <?php
    class Example implements \Serializable
    {
        //...
    }

    $serializable = new Example;

    $metable->setMeta('example', $serializable);

Plain Objects
^^^^^^^^^^^^^^

Any other objects will be converted to ``stdClass`` plain objects. You can control what properties are stored by implementing the ``JsonSerializable`` interface on the class of your stored object.

::

    <?php
    $metable->setMeta('weight', new Weight(10, 'kg'));
    $weight = $metable->getMeta('weight') // stdClass($amount = 10; $unit => 'kg');

.. note:: The ``Plank\Metable\DataType\ObjectHandler`` class should always be the last entry the ``config/metable.php`` datatypes array, as it will accept any object, causing any handlers below it to be ignored.

.. warning:: Laravel-Metable uses ``json_encode()`` and ``json_decode()`` under the hood for plain object serialization. This will cause any arrays within the object's properties to be cast to a ``stdClass`` object.


Adding Custom Data Types
------------------------

You can add support for other data types by creating a new ``Handler`` for your class, which can take care of serialization. Only objects which can be converted to a string and then rebuilt from that string should be handled. 


Define a class which implements the `Plank\\Metable\\DataType\\Handler <https://github.com/plank/laravel-metable/blob/master/src/DataType/Handler.php>`_ interface and register it to the ``'datatypes'`` array in ``config/metable.php``. The order of the handlers in the array is important, as Laravel-Metable will iterate through them and use the first entry that returns ``true`` for the ``canHandleValue()`` method for a given value. Make sure more concrete classes come before more abstract ones.
