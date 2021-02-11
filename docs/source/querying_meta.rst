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
----------------

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

A Note on Optimization
----------------------

Laravel-Metable is intended a convenient means for handling data of many different shapes and sizes. It was designed for dealing with data that only a subset of all models in a table would have any need for.

For example, you have a Page model with a template field and each template needs some number of additional fields to modify how it displays. If you have X templates which each have up to Y fields, adding all of these as columns to pages table will quickly get out of hand. Instead, appending these template fields to the Page model as meta can make handling this use case trivial.

Laravel-Metable makes it very easy to append just about any data to your models. However, for sufficiently large data sets or data that is queried very frequently, it will often be more efficient to use regular database columns instead in order to take advantage of native SQL data types and indexes. The optimal solution will depend on your use case.
