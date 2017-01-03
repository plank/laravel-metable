# Laravel-Metable

[![Travis](https://img.shields.io/travis/plank/laravel-metable/master.svg?style=flat-square)](https://travis-ci.org/plank/laravel-metable)
[![Coveralls](https://img.shields.io/coveralls/plank/laravel-metable.svg?style=flat-square)](https://coveralls.io/github/plank/laravel-metable)
[![Packagist](https://img.shields.io/packagist/v/plank/laravel-metable.svg?style=flat-square)](https://packagist.org/packages/plank/laravel-metable)

Laravel-Metable is a package for easily attaching arbitrary data to Eloquent models for Laravel 5. 

## Features

- One-to-many polymorphic relationship allows attaching data to Eloquent models without needing to adjust the database schema. 
- Data type conversion system allows data of numerous different types (null, boolean, integer, float, string, array, object, eloquent model, eloquent collection) to be stored, queried and retrieved.

## Example Usage

Attach some metadata to an eloquent model 

```php
$post = Post::create($this->request->input());
$post->setMeta('color', 'blue');
```

Query the model by its metadata

```php
$post = Post::whereMeta('color', 'blue');
```

Retrieve the metadata from a model

```php
$value = $post->getMeta('color');
```

## Installation

Add the package to your Laravel app using composer

```bash
composer require plank/laravel-metable
```

Register the package's service provider in `config/app.php`

```php
'providers' => [
    ...
    Plank\Metable\MetableServiceProvider::class,
    ...
];
```

Publish the config file (`config/metable.php`) and migration file (`database/migrations/####_##_##_######_create_metable_table.php`) of the package using artisan.

```bash
php artisan vendor:publish --provider="Plank\Metable\MetableServiceProvider"
```

Run the migrations to add the required table to your database.

```bash
php artisan migrate
```

Add the `Metable` trait to any eloquent model class that you want to be able toattach metadata to.


```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Plank\Metable\Metable;

class Post extends Model
{
	use Metable;

	//...
}
```

## Documentation

Read the documentation [here](http://laravel-metable.readthedocs.io/en/latest/).

## License

This package is released under the MIT license (MIT).

## About Plank

[Plank](http://plankdesign.com) is a web development agency based in Montreal, Canada.

