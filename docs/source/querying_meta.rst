Querying Meta
=============

.. highlight:: php

The ``Metable`` trait provides a number of query scopes to facilitate modifying queries based on the meta attached to your models

Checking for Presence of a key
------------------------------

To only return records that have a value assigned to a particular key, you can use ``whereHasMeta()``. You can also pass an array to this method, which will cause the query to return any models attached to one or more of the provided keys.

::

    <?php
    $models = MyModel::whereHasMeta('notes')->get();
    $models = MyModel::whereHasMeta(['queued_at', 'sent_at'])->get();

If you would like to restrict your query to only return models with meta for `all` of the provided keys, you can use ``whereHasMetaKeys()``.

::

    <?php
    $models = MyModel::whereHasMetaKeys(['step1', 'step2', 'step3'])->get();

You can also query for records that does not contain a meta key using the ``whereDoesntHaveMeta()``. Its signature is identical to that of ``whereHasMeta()``.

::

    <?php 
    $models = MyModel::whereDoesntHaveMeta('notes')->get();
    $models = MyModel::whereDoesntHaveMeta(['queued_at', 'sent_at'])->get();

Comparing value
---------------

You can restrict your query based on the value stored for a particular meta key. Query scopes for selecting records based on the value of attached meta come in two main flavors: string-based and numeric-based, which informs which indexes are used to lookup the data efficiently. Different data types may support filtering and ordering by different query scopes. Refer to the :ref:`Data Types <data-types>` section for more information.

The value passed to to the query scope will be converted to the appropriate data type before being passed to the database. As such any value that you can pass to the ``Metable::setMeta()`` method can be passed to the query scope, as long as the data type is supported by the operation.

String Value Query Scopes
^^^^^^^^^^^^^^^^^^^^^^^^^

All string-based query scopes using lexicographic comparison to look up values. This means that the values are compared alphabetically as strings. This can lead to unexpected results when comparing numbers, e.g. ``'11'`` is greater than ``'100'``.

The ``whereMeta()`` method can be used to compare the value using any of the operators accepted by the Laravel query builder's ``where()`` method.

::

    <?php
    // omit the operator (defaults to '=')
    $models = MyModel::whereMeta('status', 'success')->get();

    // greater than
    $models = MyModel::whereMeta('name', '>', 'M')->get();

    // like
    $models = MyModel::whereMeta('summary', 'like', 'Once upon a time%')->get();

    //etc.

The ``whereMetaIn()`` method and its inverse are also available to find records where the value is matches one of a predefined set of options.

::

    <?php
    $models = MyModel::whereMetaIn('country', ['CA', 'US', 'MX'])->get();
    $models = MyModel::whereMetaNotIn('currency', ['USD', 'GBP', 'EUR'])->get();

The ``whereMetaBetween()`` and its inverse method can be used to compare records to a range.

::

    <?php
    $models = MyModel::whereMetaBetween('country_code', 'AD', 'AZ')->get();
    $models = MyModel::whereMetaNotBetween('name', 'a', 'm')->get();

Numeric Value Query Scopes
^^^^^^^^^^^^^^^^^^^^^^^^^

Numeric values are indexed in a decimal column that supports up to 20 integral digits and 16 fractional digits (enough to support 64-bit integers and floats at full precision). This allows for a wide range of values to be stored and queried efficiently. The numeric query scopes use numeric comparison to look up values.

Query scopes are available for numeric values as for string values.

::

    <?php
    $models = MyModel::whereMetaNumeric('counter', '>', 42)->get();
    $models = MyModel::whereMetaInNumeric('http_code', [401, 403])->get();
    $models = MyModel::whereMetaNotInNumeric('department', [])->get();
    $models = MyModel::whereMetaBetweenNumeric('completed_at', Carbon::yesterday(), Carbon::today())->get();
    $models = MyModel::whereMetaNotBetweenNumeric('percentile', 90, 100)->get();

Other Query Scopes
^^^^^^^^^^^^^^^^^^

You can look up if a meta key contains a reference to a particular model using the ``whereMetaIsModel()`` method.

::

    <?php
    // find models that reference a particular model ID
    $models = MyModel::whereMetaIsModel(\App\MyOtherModel::class, $id)->get();
    $models = MyModel::whereMetaIsModel($otherModelInstance)->get();

    // find models that reference a any instance of a model class
    $models = MyModel::whereMetaIsModel(\App\MyOtherModel::class)->get();

If you specifically assigned a meta key to `null`, you can query for models that have a `null` value for that key using the ``whereMetaNull()`` method.

::

    <?php
    $models = MyModel::whereMetaNull('notes')->get();


Ordering results
----------------

You can apply an order by clause to the query to sort the results by the value of a meta key.

::

    <?php
    // lexicographic order
    $models = MyModel::orderByMeta('nickname', 'asc')->get();

    // numeric order
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