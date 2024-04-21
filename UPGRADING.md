# Upgrading

## 5.X -> 6.X

* Minimum PHP version moved to 8.1
* Minimum Laravel version moved to 10
* Some methods have had their signatures adjusted to use PHP 8+ mixed and union types. If extending any class or implementing any interface from this package, method signatures may need to be updated.
* A new schema migration has been added which adds three new columns to the meta table and improves indexing for querying by meta values.
* Add the `PureEnumHandler` and `BackedEnumHandler` classes to the `datatypes` config. These handlers provide support for storing enum values as Meta.
* Recommended to add the `SignedSerializeHandler` to the end of `datatypes` config (catch-all).
* The `SerializableHandler`, `ArrayHandler`, and `ObjectHandler` data types have been deprecated in favor of the new `SignedSerializeHandler`. If you have any Meta encoded using any of these data types, you should continue to include them in the `datatypes` config _after_ the `SignedSerializeHandler` to ensure that existing values will continue to be properly decoded, but new values will use the new encoding. Once all old values have been migrated, you may remove the deprecated data types from the `datatypes` config.
* For security reasons, if you have any existing Meta encoded using `SerializableHandler`, you must configure the `metable.serializableHandlerAllowedClasses` config to list classes that are allowed to be unserialized. Otherwise, all objects will be returned as `__PHP_Incomplete_Class`. This config may be set to `true` to disable this security check and allow any class, but this is not recommended.
* If you have any custom data types, you will need to implement the new methods from the `HandlerInterface`:
  * `getStringValue(): ?string` and `getNumericValue(): null|int|float`: These are used to populate the new indexed search columns. You may return `null` if the value cannot be converted into the specified format or does not need to be searchable.
  * `isIdempotent(): bool`: This method should indicate whether multiple calls to the `serialize()` method with the same value will produce the same serialized output. This is used to determine if the complete serialized value can be used when searching for meta values.  
  * `useHmacVerification(): bool`: if the integrity of the serialized data should be verified with a HMAC, return `true`. If unserializing this data type is safe without HMAC verification, you may return `false`. 
* Once you have applied the schema migration and configured the changes to the `datatypes` config, you should run the `metable:refresh` Artisan command to update all existing meta values to use the new types and populate the index columns. After this command has been run, you may remove the deprecated data types from the `datatypes` config. 
* Review the documentation about which data types can be queried with the various `whereMeta*` and `whereMeta*Numeric` query scopes. If you are querying the serialized `value` column directly, be aware that the formatting of array/object data types may have changed.

## 4.X -> 5.X
- New migration file added which adds a new composite unique index to the meta table on `metable_type`, `metable_id`, and `key`. Make sure that you have no duplicate keys for a given entity (previously possible as a race condition) before applying the new migration.  

## 3.X -> 4.X
- Database migration files are now served from within the package. In your migrations table, rename the `XXXX_XX_XX_XXXXXX_create_meta_table.php` entry to `2017_01_01_000000_create_meta_table.php` and delete your local copy of the migration file from the /database/migrations directory. If any customizations were made to the table, those should be defined as one or more separate ALTER table migrations.