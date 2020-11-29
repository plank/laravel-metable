Introduction
=============

.. highlight:: php

Laravel-Metable is a package for easily attaching arbitrary data to Eloquent models for Laravel 5.

Features
---------------

* One-to-many polymorphic relationship allows attaching data to Eloquent models without needing to adjust the database schema.
* Type conversion system allows data of numerous different scalar and object types to be stored, queried and retrieved. See the list of supported :ref:`datatypes`.

Installation
-------------

1. Add the package to your Laravel app using composer

::

    composer require plank/laravel-metable


2. Register the package's service provider in ``config/app.php``. In Laravel versions 5.5 and beyond, this step can be skipped if package auto-discovery is enabled.

::

    <?php
    'providers' => [
        ...
        Plank\Metable\MetableServiceProvider::class,
        ...
    ];


3. Publish the config file (``config/metable.php``) of the package using artisan.

::

    php artisan vendor:publish --provider="Plank\Metable\MetableServiceProvider"


4. Run the migrations to add the required table to your database.

::

    php artisan migrate


5. Add the `Plank\\Metable\\Metable <https://github.com/plank/laravel-metable/blob/master/src/Metable.php>`_ trait to any eloquent model class that you want to be able to attach metadata to.

Example Usage
----------------

Attach some metadata to an eloquent model

::

    <?php
    $post = Post::create($this->request->input());
    $post->setMeta('color', 'blue');


Query the model by its metadata

::

    <?php
    $post = Post::whereMeta('color', 'blue');

Retrieve the metadata from a model

::

    <?php
    $value = $post->getMeta('color');
