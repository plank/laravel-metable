Handling Meta
=============

.. highlight:: php

before you can attach meta to an Eloquent model, you must first add the ``Metable`` trait to your Eloquent model.

::

    <?php

    namespace App;

    use Plank\Metable\Metable;
    use Illuminate\Database\Eloquent\Model;

    class Page extends Model
    {
        use Metable;

        // ...
    }

.. note::
    The Metable trait adds a ``meta()`` relationship to the model. However, it also keeps meta keys indexed separately to speed up reads. As such, it is recommended to not modify this relationship directly and to instead only use the methods described in this document.

Attaching Meta
--------------

Attach meta to a model with the ``setMeta()`` method. The method accepts two arguments: a string to use as a key and a value. The value argument will accept a number of different inputs, which will be converted to a string for storage in the database. See the list of a supported :ref:`datatypes`.

::

    <?php
    $model->setMeta('key', 'value');

To set multiple meta key and value pairs at once, you can pass an associative array or collection to ``setManyMeta()``. The meta will be added to the model.

::

    <?php
    $model->setManyMeta([
        'name' => 'John Doe',
        'age' => 18,
    ]);

To replace existing meta with a new set of meta, you can pass an associative array or collection to ``syncMeta()``. **All existing meta will be removed before the new meta is attached.**

::

    <?php
    $model->syncMeta([
        'name' => 'John Doe',
        'age' => 18,
    ]);

Encrypting Meta
---------------

If storing sensitive data, you can instruct the package to encrypt a meta value when it is stored in the database. Encrypted values are automatically decrypted when retrieved. To encrypt a value, use the ``setMetaEncrypted()`` method or pass ``true`` as the third argument to the ``setMeta()`` method.

::

        <?php
        $model->setMetaEncrypted('secret', 'sensitive data');
        $model->setMeta('secret', 'sensitive data', true);

Data of any type can be encrypted. Encrypted values are never searchable or sortable with query scopes.

Retrieving Meta
---------------

You can retrieve the value of the meta at a given key with the ``getMeta()`` method. The value should be returned in the same format that it was stored. For example, if an array is set, you will receive an array back when retrieving it.

::

    <?php

    $model->setMeta('age', 18);
    $model->setMeta('approved', true);
    $model->setMeta('accessed_at', Carbon::now());

    //reload the model from the database
    $model = $model->fresh();

    $age = $model->getMeta('age'); //returns an integer
    $approved = $model->getMeta('approved'); //returns a boolean
    $accessDate = $model->getMeta('accessed_at'); //returns a Carbon instance

    //etc.

Once loaded, all meta attached to a model instance are cached in the model's ``meta`` relationship. As such, successive calls to ``getMeta()`` will not hit the database repeatedly.

Similarly, the unserialized value of each meta is cached once accessed. This is particularly relevant for attached :ref:`eloquent_models` and similar database-dependant objects.

Setting a new value for a key automatically updates all caches.

Default Values
^^^^^^^^^^^^^^

You may pass a second parameter to the ``getMeta()`` method in order to specify a default value to return if no meta has been set at that key.

::

    <?php
    $model->getMeta('status', 'draft'); // will return 'draft' if not set

Alternatively, you may set default values as key-value pairs on the model itself, instead of specifying them at each individual call site. If a default has been defined from this property and a value is also passed as to the default parameter, the parameter will take precedence.

::

    <?php
    class ExampleMetable extends Model {
        use Metable;

        protected $defaultMetaValues = [
            'color' => '#000000'
        ];

        //...
    }

::

    <?php
    $model->getMeta('color'); // will return '#000000' if not set
    $model->getMeta('color', null); // will return null if not set
    $model->getMeta('color', '#ffffff'); // will return '#ffffff' if not set


.. note:: If a falsey value (e.g. ``0``, ``false``, ``null``, ``''``) has been manually set for the key, that value will be returned instead of the default value. The default value will only be returned if no meta exists at the key.

Casting Meta
------------

You can enforce that any meta attached to a particular key is always of a particular data type by specifying casts on the Metable model. Casts can be defined by specifying a $metaCasts attribute, or by adding a ``metaCasts(): array`` methods to the model.

::

    <?php
    class ExampleMetable extends Model {
        use Metable;

        protected $metaCasts = [
            'optin' => 'boolean',
            'age' => 'integer',
            'secret' => 'encrypted:string',
            'parent' => ExampleMetable::class,
            'children' => 'collection:\App\ExampleMetable',
        ];

        // equivalent to:
        protected function metaCasts(): array
        {
            return [
                'optin' => 'boolean',
                'age' => 'integer',
                'secret' => 'encrypted:string',
                'parent' => ExampleMetable::class,
                'children' => 'collection:\App\ExampleMetable',
            ];
        }

    }

All `cast types supported by Eloquent<https://laravel.com/docs/11.x/eloquent-mutators#attribute-casting>`_ are supported, with the following modifications:

- Casts are applied on write, not read. This means that the value will be serialized and stored in the database in the specified format, and indexes will be populated in a consistent manner. However, old data prior to the addition of the cast will not be automatically converted.
- All casts ignore values of ``null``. If a value is set to ``null``, it will be stored as ``null`` in the database, and will not be cast to the specified type.
- The ``encrypted`` cast will tell the package to always encrypt the value of that key, even if the 3rd parameter of ``setMeta()`` is not set to ``true``.
- The ``encrypted:`` cast prefix can be combined with any other cast type to convert the value to the specified type before encrypting it.
- when a class name is provided as a cast, if it implements ``\Illuminate\Contracts\Database\Eloquent\Castable``, it will be used to cast the value per the interface. Otherwise, it will enforce that the value is an instance of that class. If an instance of a different class is provided, an exception will be thrown. If the class is an Eloquent model, and an an integer or string is provided, it will attempt to retrieve the model from the database.
- The ``collection`` cast will preserve ``Illuminate\Database\Eloquent\Collection`` instances and contents, using the ``Plank\Metable\DataType\ModelCollection`` data type to store them. If passed a single model instance, it will be wrapped in an eloquent collection. A class name can be provided as an argument to enforce that the collection contains only instances of that class.

Retrieving All Meta
-------------------

To retrieve a collection of all meta attached to a model, expressed as key and value pairs, use ``getAllMeta()``.

::

    <?php
    $meta = $model->getAllMeta();


Checking For Presence of Meta
-----------------------------

You can check if a value has been assigned to a given key with the ``hasMeta()`` method.

::

    <?php
    if ($model->hasMeta('background-color')) {
        // ...
    }

.. note:: This method will return ``true`` even if a falsey value (e.g. ``0``, ``false``, ``null``, ``''``) has been manually set for the key.


Deleting Meta
-------------

To remove the meta stored at a given key, use ``removeMeta()``.

::

    <?php
    $model->removeMeta('preferred_language');

To remove multiple meta at once, you can pass an array of keys to ``removeManyMeta()``.

::

    <?php
    $model->removeManyMeta([
        'preferred_language',
        'store_currency',
        'user_timezone',
    ]);

To remove all meta from a model, use ``purgeMeta()``.

::

    <?php
    $model->purgeMeta();

Attached meta is automatically purged from the database when a ``Metable`` model is manually deleted. Meta will `not` be cascaded if the model is deleted by the query builder.

::

    <?php
    $model->delete(); // will delete attached meta
    MyModel::where(...)->delete() // will NOT delete attached meta


Eager Loading Meta
------------------

When working with collections of ``Metable`` models, be sure to eager load the meta relation for all instances together to avoid repeated database queries (i.e. N+1 problem).

Eager load from the query builder:

::

    <?php
    $models = MyModel::with('meta')->where(...)->get();

Lazy eager load from an Eloquent collection:

::

    <?php
    $models->load('meta');

You can also instruct your model class to `always` eager load the meta relationship by adding ``'meta'`` to your model's ``$with`` property.

::

    <?php

    class MyModel extends Model {
        use Metable;

        protected $with = ['meta'];
    }


Meta As Attributes
------------------

If you prefer to access meta as if they were attributes of the model, you can use the ``MetableAttributes`` trait insin addition to the ``Metable`` trait. This will allow you to access meta as if they were attributes of the model by prefixing them with ``meta_``. Meta attributes can be combined with type annotations, casts and/or default values to provide consistent typing. This can be useful for IDE completions and static analysis, as well as for use in Blade templates.

::

    <?php

    namespace App;

    use Plank\Metable\Metable;
    use Plank\Metable\MetableAttributes;
    use Illuminate\Database\Eloquent\Model;

    /**
        * @property bool $meta_approved
        * @property \Carbon\Carbon $meta_published_at
        * @property int $meta_likes
        */
    class Page extends Model
    {
        use Metable, MetableAttributes;

        $metaCasts = [
            'approved' => 'boolean',
            'published_at' => 'datetime',
            'likes' => 'integer',
        ];

        $defaultMetaValues = [
            'approved' => false,
            'published_at' => null,
            'likes' => 0,
        ];

        // ...
    }

    $page = new Page();
    $page->meta_likes = 5; // equivalent to $page->setMeta('likes', 5);
    $page->fill(['meta_approved' => true, 'meta_published_at' => now()]); // equivalent to $page->setManyMeta([...]);
    if ($page->meta_likes > 0) {} // equivalent $page->getMeta('likes');
    unset($page->meta_likes); // equivalent to $page->removeMeta('likes');


Most attribute operations will translate meta attributes to their corresponding meta operations. However, the ``getAttributes()`` method will **not** include meta attributes. The ``getMetaAttributes()`` method can be used to retrieve all meta values keyed by their attribute name.

The ``toArray()`` method will include meta attributes by default. The ``$visible``/``$hidden`` properties of the model will be respected if any meta attributes are listed. The ``makeMetaHidden()`` method can be used to quickly hide all currently assigned meta attributes from the array representation of the model.