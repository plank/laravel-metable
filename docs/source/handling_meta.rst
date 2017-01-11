Handling Meta
===========================================

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
------------------

Attach meta to a model with the ``setMeta()`` method. The method accepts two arguments: a string to use as a key and a value. The value argument will accept a number of different inputs. See the list of a supported :ref:`datatypes`. 

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


Checking Meta
-----------------

You can check if a value has been assigned to a given key with the ``hasMeta()`` method.

::

	<?php 
	if ($model->hasMeta('background-color')) {
		// ...
	}

.. note:: This method will return ``true`` even if a falsey value (e.g. ``0``, ``false``, ``null``) has been manually set for the key. 

Retrieving Meta
-------------------

You can retrieve the value of the meta at a given key with the ``getMeta()`` method.

::

	<?php
	$model->getMeta('status');

You may pass a second parameter to the method in order to specify a default value to return if no meta had been set at that key.

::

	<?php
	$model->getMeta('status', 'draft');

.. note:: If a falsey value (e.g. ``0``, ``false``, ``null``) has been manually set for the key, that value  will be returned instead of the default value. The default value will only be returned if no meta exists at the key.


Deleting Meta
-----------------

Querying Meta
--------------