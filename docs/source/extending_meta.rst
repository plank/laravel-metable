Extending Meta
==============

.. highlight:: php

Here are some mechanisms provided to customize the behaviour of this package.

Adjusting Schema
----------------

If you wish to modify the database schema of the package, it is recommended that you copy the base migration files provided by this package into your application's database/migration folder. If the file names match exactly, the ones provided by this package will be ignored.

If the customization that you are applying would cause conflicts with future migrations (e.g. changing table name), then it is recommended to set the ``metable.applyMigrations`` config to ``false``, which will disable future migrations from being run. Do note that you may need to apply migrations provided in future major versions of the package manually to avoid conflicts.

Adjusting the Model
-------------------

You can modify the Meta model by simply extending the class.

If you wish to use the same custom ``Meta`` subclass for all ``Metable`` models, you can register the fully-qualified class name to the ``metable.model`` config.

If you would prefer to use different ``Meta`` subclasses for different entities (e.g. to keep data in separate tables), you can override the ``Metable::getMetaClassName()`` method of each model to specify the desired ``Meta`` class to use for each entity.

::

    <?php
    class UserMeta extends Meta
    {
        protected $table = 'user_meta';
    }

    class ProductMeta extends Meta
    {
        protected $table = 'product_meta';
    }

    class User extends Model
    {
        use Metable;

        protected function getMetaClassName(): string
        {
            return UserMeta::class;
        }
    }

    class Product extends Model
    {
        use Metable;

        protected function getMetaClassName(): string
        {
            return ProductMeta::class;
        }
    }
