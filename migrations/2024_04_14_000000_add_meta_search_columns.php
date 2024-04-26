<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddMetaSearchColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meta', function (Blueprint $table) {
            $table->decimal('numeric_value', 36, 16)->nullable();
            $table->string('hmac', 64)->nullable();

            $table->dropIndex(['key', 'metable_type']);
            $table->dropIndex(['key']);
            $table->index(['key', 'metable_type', 'numeric_value']);

            $stringIndexLength = (int)config('metable.stringValueIndexLength', 255);
            if ($stringIndexLength > 0 && $driver = $this->detectDriverName()) {
                if (in_array($driver, ['mysql', 'mariadb'])) {
                    $table->rawIndex(
                        "metable_type, key, value($stringIndexLength)",
                        'value_string_prefix_index'
                    );
                } elseif (in_array($driver, ['pgsql', 'sqlite'])) {
                    $table->rawIndex(
                        "metable_type, key, substr(value, 1, $stringIndexLength)",
                        'value_string_prefix_index'
                    );
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meta', function (Blueprint $table) {
            $stringIndexLength = (int)config('metable.stringValueIndexLength', 255);
            if ($stringIndexLength > 0
                && in_array($this->detectDriverName(), ['mysql', 'mariadb', 'pgsql', 'sqlite'])
            ) {
                $table->dropIndex('value_string_prefix_index');
            }

            $table->dropIndex(['key', 'metable_type', 'numeric_value']);
            $table->index(['key']);
            $table->index(['key', 'metable_type']);
            $table->dropColumn('hmac');
            $table->dropColumn('numeric_value');
        });
    }

    private function detectDriverName(): ?string
    {
        /** @var \Illuminate\Database\Migrations\Migrator $migrator */
        $migrator = app('migrator');
        $repository = $migrator->getRepository();

        if (method_exists($repository, 'getConnectionResolver')) {
            $resolver = $repository->getConnectionResolver();
        } else {
            $resolver = DB::getFacadeRoot();
        }

        return $resolver->connection(
            $this->getConnection() ?? $migrator->getConnection()
        )->getDriverName();
    }
}
