# Changelog

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
