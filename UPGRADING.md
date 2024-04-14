# Upgrading

## 5.X -> 6.X

* Minimum PHP version moved to 8.1
* Minimum Laravel version moved to 10
* Some methods have had their signatures adjusted to use PHP 8+ mixed and union types. If extending any class or implementing any interface from this package, method signatures may need to be updated.
* Recommended to add the `SerializeHandler` to the end of `datatypes` config (catch-all).
* The `SerializableHandler`, `ArrayHandler`, and `ObjectHandler` data types have been deprecated in favor of the new `SerializeHandler`. If you have any Meta encoded using any of these data types, you should continue to include them in the `datatypes` config _after_ the `SerializeHandler` to ensure that existing values will continue to be properly decoded, but new values will use the new encoding. Once all old values have been migrated, you may remove the deprecated data types from the `datatypes` config.
* For security reasons, if you have any existing Meta encoded using `SerializableHandler`, you must configure the `metable.options.serializable.allowedClasses` config to list classes that are allowed to be unserialized. Otherwise, all objects will be returned as `__PHP_Incomplete_Class`. This config may be set to `true` to disable this security check and allow any class, but this is not recommended.

## 4.X -> 5.X
- New migration file added which adds a new composite unique index to the meta table on `metable_type`, `metable_id`, and `key`. Make sure that you have no duplicate keys for a given entity (previously possible as a race condition) before applying the new migration.  

## 3.X -> 4.X
- Database migration files are now served from within the package. In your migrations table, rename the `XXXX_XX_XX_XXXXXX_create_meta_table.php` entry to `2017_01_01_000000_create_meta_table.php` and delete your local copy of the migration file from the /database/migrations directory. If any customizations were made to the table, those should be defined as one or more separate ALTER table migrations.