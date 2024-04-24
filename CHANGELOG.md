# Changelog

## 6.0.0

Version 6 contains a number of changes to improve the security and performance of the package. Refer to the [UPGRADING.md](UPGRADING.md) file for detailed instructions on how to upgrade from version 5. 

### Compatibility

- Added support for PHP 8.3
- Droppped support for PHP 8.0 and below
- Added support for Laravel 10 and 11
- Dropped support Laravel versions 9 and below
- Adjusted some method signatures with PHP 8+ mixed and union types
- New schema migration adding two new columns and improving indexing for searching by meta values. See [UPGRADING.md](UPGRADING.md) for details

### Data Types

- Added `SignedSerializeHandler` as a catch-all datatype, which will attempt to serialize the data using PHP's `serialize()` function. The payload is cryptographically signed with an HMAC before being stored in the database to prevent PHP object injection attacks.
- Deprecated `SerializableHandler` in favor of the new `SignedSerializeHandler` datatype. The `SerializableHandler` will be removed in a future release. In the interim, added the `metable.options.serializable.allowedClasses` config to protect against unserializing untrusted data.
- Deprecated `ArrayHandler` and `ObjectHandler`, due to the ambiguity of nested array/objects switching type. These will be removed in a future release. The `SignedSerializeHandler` should be used instead.
- Added `PureEnumHandler` and `BackedEnumHandler` which adds support for storing enum values as Meta.
- Added `StringableHandler` which adds support for storing `Illuminate\Support\Stringable` objects as Meta.
- Added `DateTimeImmutableHandler` which adds support for storing `DateTimeImmutable`/`CarbonImmutable` objects as Meta.
- `ModelHandler` will now validate that the encoded class is a valid Eloquent Model before attempting to instantiate it during unserialization. If the class is invalid, the meta value will return `null`.
- `ModelHandler` will no longer throw a model not found exception if the model no longer exists. Instead, the meta value will return `null`. This is more in line with the existing behavior of the `ModelCollectionHandler`.
- `ModelCollectionHandler` will now validate that the encoded collection class is a valid Eloquent collection before attempting to instantiate it during unserialization. If the class is invalid,  an instance of `Illuminate\Database\Eloquent\Collection` will be used instead.
- `ModelCollectionHandler` will now validate that the encoded class of each entry is a valid Eloquent Model before attempting to instantiate it during unserialization. If the class is invalid, that entry in the collection will be omitted.
- Added `getStringValue(): ?string` and `getNumericValue(): null|int|float` methods to `HandlerInterface` which should convert the original value into a format that can be indexed, if possible.
- Added `isIdempotent(): bool` method to `HandlerInterface` which should indicate whether multiple calls to the `serialize()` method with the same value will produce the same serialized output. This is used to determine if the complete serialized value can be used when searching for meta values.
- Added `useHmacVerification(): bool` method to `HandlerInterface` which should indicate whether the integrity of the serialized data should be verified with an HMAC.

### New Commands

- Added `metable:refresh` artisan command which will decode and re-encode all meta values in the database. This is useful if you have changed the data type handlers and need to update the serialized data and indexes in the database.

### Searching Metables By Meta Value 

- the Metable `whereMeta()`, `whereMetaIn()`, and `orderByMeta()` query scopes will now scan the indexed `string_value` column instead of the serialized `value` column. This greatly improves performance when searching for meta values against larger datasets.
- `whereMetaNumeric()` and `orderByMetaNumeric()` query scopes will now scan the indexed `numeric_value` column instead of the serialized `value` column. This greatly improves performance when searching for meta values against larger datasets.
- `whereMetaNumeric()` query scope will now accept a value of any type. It will be converted to an integer or float by the handler. This is more consistent with the behaviour of the other query scopes.  
- Added additional query scopes to more easily search meta values based on different criteria:
  - `whereMetaInNumeric()`
  - `whereMetaNotIn()`
  - `whereMetaNotInNumeric()`
  - `whereMetaBetween()`
  - `whereMetaBetweenNumeric()`
  - `whereMetaNotBetween()`
  - `whereMetaNotBetweenNumeric()`
  - `whereMetaIsNull()`
  - `whereMetaIsModel()`
- If the data type handlers cannot convert the search value provided to a whereMeta* query scope to a string or numeric value (as appropriate for the method), then an exception will be thrown.

### Metable Casting

- Added support for casting meta values to specific types by defining the `$castMeta` property or `castMeta(): array` method on the model. This is similar to the `casts` property of Eloquent Models used for attributes. All cast types supported by Eloquent Models are also available for Meta values.Values will be cast before values are stored in the database to ensure that they are indexed consistently
- the `encrypted:` cast prefix can be combined with any other cast type to cast to the desired type before the value is encrypted to be stored it in the database. Encrypted values are not searchable.
- A value of `null` is ignored by all cast types. 
- Added `mergeMetaCasts()` method which can be used to override the defined cast on a meta key at runtime.

### Meta
- Added `$meta->string_value` and `$meta->numeric_value` attributes, which are used for optimizing queries filtering by meta value
- Added `$meta->hmac` attribute, which is used by some data type handlers to validate that the payload has not been tampered with.
- Added `$meta->raw_value` virtual attribute, which exposes the raw serialized value of the meta key. This is useful for debugging purposes.
- Added `encrypt()` method, used internally by the `Metable::setMetaEncrypted()` method

# 5.0.1 - 2021-09-19
- Fixed `setManyMeta()` not properly serializing certain types of data.

# 5.0.0 - 2021-02-11
- New schema migration: improved database indexing. See [UPGRADING.md](UPGRADING.md) for details.
- Added config `meta.applyMigrations`. When set to false, migration paths will not be loaded from the package. Use this if you wish to override the default schema migrations provided with the package.
- Added `setManyMeta()` to bulk insert/update multiple keys to a model. Requires Laravel 8.0+ for optimal performance.
- Added `removeManyMeta()` to bulk delete multiple keys from a model.
- Fixed `removeMeta()` method causing an error if called with a non-existent key.
- Fixed a minor bug with `setMeta()` creating duplicates in the cached meta relation when updating a key.

# 4.0.0 - 2020-10-12
- fixed support for Laravel 8.0 migration squashing
- Migration files are now loaded from the package itself instead of being published to the local /database/migrations directory. This may cause conflicts when upgrading, see [UPGRADING.md](UPGRADING.md) for mitigation steps. 

# 3.0.0 - 2020-09-13
- Added support for Laravel 8.0 (Thanks @saulens!)
- Moved minimum requirements to PHP 7.3 and Laravel 6.0+

# 2.1.1 - 2020-06-15
- Fixed array unpacking issue when queuing Metable models

# 2.1.0 2020-03-06
- Added Laravel 7.0 support (Thanks @saulens22!)

# 2.0.1 - 2020-03-05
- The `joinMetaTable()` function now uses `getMorphClass()` for `Metable` trait to assist with single table inheritance (Thanks @mbryne!)

# 2.0.0 - 2019-09-12
- Moved minimum requirements to PHP 7.2 and Laravel 5.6+
- Added a number of missing return types.

## 1.1.0 - 2018-05-15
- Added `whereDoesntHaveMeta()` query scope (Thanks @tormjens!)

## 1.0.4 - 2017-08-20
- Added Laravel 5.5 package autodiscovery
- Meta keys are now correctly case sensitive throughout the package (Thanks @Luukvdo!)
- Fixed some PHPDoc typehints (Thanks @Luukvdo!)

## 1.0.3 - 2017-03-21
- Fixed some compatibility bugs with MySQL

## 1.0.2 - 2017-02-03
- Removed illegal default from value column in migration

## 1.0.1 - 2017-01-30
- Added Laravel 5.4 Compatibility

## 1.0.0 - 2017-01-16
- Initial Release
