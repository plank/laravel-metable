# Laravel-Metable

[![Travis](https://img.shields.io/travis/plank/laravel-metable/master.svg?style=flat-square)](https://travis-ci.org/plank/laravel-metable)
[![Coveralls](https://img.shields.io/coveralls/plank/laravel-metable.svg?style=flat-square)](https://coveralls.io/github/plank/laravel-metable)
[![StyleCI](https://styleci.io/repos/79148832/shield?branch=master)](https://styleci.io/repos/79148832)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/83f303ee-321e-4860-8638-cc1f513b7fe5.svg?style=flat-square)](https://insight.sensiolabs.com/projects/83f303ee-321e-4860-8638-cc1f513b7fe5)
[![Packagist](https://img.shields.io/packagist/v/plank/laravel-metable.svg?style=flat-square)](https://packagist.org/packages/plank/laravel-metable)

Laravel-Metable is a package for easily attaching arbitrary data to Eloquent models for Laravel 5.6+.

## Features

- One-to-many polymorphic relationship allows attaching data to Eloquent models without needing to adjust the database schema.
- Type conversion system allows data of numerous different scalar and object types to be stored and retrieved. See the documentation for the list of supported types.

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

1. Add the package to your Laravel app using composer

```bash
composer require plank/laravel-metable
```

2. Register the package's service provider in `config/app.php`. In Laravel versions 5.5 and beyond, this step can be skipped if package auto-discovery is enabled.

```php
'providers' => [
    ...
    Plank\Metable\MetableServiceProvider::class,
    ...
];
```

3. Publish the config file (`config/metable.php`) and migration file (`database/migrations/####_##_##_######_create_metable_table.php`) of the package using artisan.

```bash
php artisan vendor:publish --provider="Plank\Metable\MetableServiceProvider"
```

4. Run the migrations to add the required table to your database.

```bash
php artisan migrate
```

5. Add the `Plank\Metable\Metable` trait to any eloquent model class that you want to be able to attach metadata to.


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

The full documentation is available on [ReadTheDocs](http://laravel-metable.readthedocs.io/en/latest/).

## License

This package is released under the MIT license (MIT).

## About Plank

[Plank](http://plankdesign.com) is a web development agency based in Montreal, Canada.

