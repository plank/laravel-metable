Handling Meta
=============

.. highlight:: php

before you can attach meta to an Eloquent model, you must first add the eloquent trait to your model.

::

	<?php

	namespace App;

	use Plank\Mediable\Mediable;
	use Illuminate\Database\Eloquent\Model;

	class Page extends Model
	{
		use Mediable;

		// ...
	}

Attaching Meta
--------------

Attach meta to a model with the ``setMeta()`` method. The method accepts two arguments: a string to use as a key and a value. The value argument will accept a number of different inputs, which will be converted to a string for storage in the database. See the list of a supported :ref:`datatypes`. 

::

	<?php
	$model->setMeta('key', 'value');

To set multiple meta key and value pairs at once, you can pass an associative array to ``syncMeta()``.

::

	<?php
	$model->syncMeta([
		'name' => 'John Doe',
		'age' => 18,
	]);

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

You may pass a second parameter to the method in order to specify a default value to return if no meta had been set at that key.

::

	<?php
	$model->getMeta('status', 'draft');

.. note:: If a falsey value (e.g. ``0``, ``false``, ``null``, ``''``) has been manually set for the key, that value will be returned instead of the default value. The default value will only be returned if no meta exists at the key.


Once loaded, all meta attached to a model instance are cached in the model's ``meta`` relationship. As such, successive calls to ``getMeta()`` will not hit the database repeatedly. 

Similarly, the unserialized value of each meta is cached once accessed. This is particularly relevant for attached :ref:`eloquent_models` and similar database-dependant objects.

Setting a new value for a key automatically updates all caches.

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

	<?php $model->removeMeta('prefered_language');

To Remove all meta from a model, use ``purgeMeta()``.

::

	<?php $model->purgeMeta();



Querying Meta
-------------

The Metable trait provides a number of query scopes to facilitate modifying queries based on the meta attached to your models

Checking for Presence of a key
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

To only return records that have a value assigned to a particular key, you can use ``whereHasMeta()``. You can also pass an array to this method, which will cause the query to return any models attached to one or more of the provided keys.

::

	<?php
	$models = MyModel::whereHasMeta('notes')->get();
	$models = MyModel::whereHasMeta(['queued_at', 'sent_at'])->get();

If you would like to restrict your query to only return models with meta for `all` of the provided keys, you can use ``whereHasMetaKeys()``.

::

	<?php
	$models = MyModel::whereHasMetaKeys(['step1', 'step2', 'step3'])->get();

Comparing value
^^^^^^^^^^^^^^^

You can restrict your query based on the value stored at a meta key. The ``whereMeta()`` method can be used to compare the value using any of the operators accepted by the Laravel query builder's ``where()`` method.

::

	<?php
	// omit the operator (defaults to '=')
	$models = MyModel::whereMeta('letters', ['a', 'b', 'c'])->get();

	// greater than
	$models = MyModel::whereMeta('name', '>', 'M')->get();

	// like
	$models = MyModel::whereMeta('summary', 'like', '%bacon%')->get();

	//etc.

The ``whereMetaIn()`` method is also available to find records where the value is matches one of a predefined set of options.

::
	
	<?php
	$models = MyModel::whereMetaIn('country', ['CAN', 'USA', 'MEX']);


The ``whereMeta()`` and ``whereMetaIn()`` methods perform string comparison (lexicographic ordering). Any non-string values passed to these methods will be serialized to a string. This is useful for evaluating equality (``=``) or inequality (``<>``), but may behave unpredictably with some other operators for non-string data types.

::

	<?php
	// array value will be serialized before it is passed to the database
	$model->setMeta('letters', ['a', 'b', 'c']);

	// array argument will be serialized using the same mechanism
	// the original model will be found.
	$model = MyModel::whereMeta('letters', ['a', 'b', 'c'])->first();

Depending on the format of the original data, it may be possible to compare against subsets of the data using the SQL ``like`` operator and a string argument.


::

	<?php
	$model->setMeta('letters', ['a', 'b', 'c']);

	// check for the presence of one value within the json encoded array
	// the original model will be found
	$model = MyModel::whereMeta('letters', 'like', '%"b"%' )->first();


When comparing integer or float values with the ``<``, ``<=``, ``>=`` or ``>`` operators, use the ``whereMetaNumeric()`` method. This will cast the values to a number before performing the comparison, in order to avoid common pitfalls of lexicographic ordering (e.g. ``'11'`` is greater than ``'100'``).

::

	<?php
	$models = MyModel::whereMetaNumeric('counter', '>', 42)->get();

Ordering results
^^^^^^^^^^^^^^^^

You can apply an order by clause to the query to sort the results by the value of a meta key.

::

	<?php
	// order by string value
	$models = MyModel::orderByMeta('nickname', 'asc')->get();

	//order by numeric value
	$models = MyModel::orderByMetaNumeric('score', 'desc')->get();

By default, all records matching the rest of the query will be ordered. Any records which have no meta assigned to the key being sorted on will be considered to have a value of ``null``.

To automatically exclude all records that do not have meta assigned to the sorted key, pass ``true`` as the third argument. This will perform an inner join instead of a left join when sorting.

::

	<?php
	// sort by score, excluding models which have no score
	$model = MyModel::orderByMetaNumeric('score', 'desc', true)->get();

	//equivalent to, but more efficient than
	$models = MyModel::whereHasMeta('score')
		->orderByMetaNumeric('score', 'desc')->get();

Eager Loading Meta
------------------

When working with collections of Metable models, be sure to eager load the meta relation for all instances together to avoid repeated database queries (i.e. N+1 problem).

Eager load from the query builder:

::

	<?php 
	$models = MyModel::with('meta')->where(...)->get();

Lazy eager load from an Eloquent collection:

::

	<?php
	$models->load('meta');

A Note on Optimization
----------------------

Laravel-Metable is intended a convenient means for handling data of many different shapes and sizes. It was designed for dealing with data that only a subset of all models in a table would have any need for. 

For example, you have a Page model with a template field and each template needs some number of additional fields to modify how it displays. If you have X templates which each have up to Y fields, adding all of these as columns to pages table will quickly get out of hand. Instead, appending these template fields to the Page model as meta can make handling this use case trivial.

Laravel-Metable makes it very easy to append just about any data to your models. However, for sufficiently large data sets or data that is queried very frequently, it will often be more efficient to use regular database columns instead in order to take advantage of native SQL data types and indexes. The optimal solution will depend on your use case. 