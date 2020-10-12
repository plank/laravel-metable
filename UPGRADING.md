# Upgrading

## 3.0.0 -> 4.0.0
- Database migration files are now served from within the package. In your migrations table, rename the `XXXX_XX_XX_XXXXXX_create_meta_table.php` entry to `2017_01_01_000000_create_meta_table.php` and delete your local copy of the migration file from the /database/migrations directory. If any customizations were made to the table, those should be defined as one or more separate ALTER table migrations.