<?php

use Illuminate\Database\Connection;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
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
            $connection = $this->detectConnectionInUse();
            if ($stringIndexLength > 0 && $driver = $connection?->getDriverName()) {
                $grammar = $connection->getQueryGrammar();
                if (in_array($driver, ['mysql', 'mariadb'])) {
                    $table->rawIndex(
                        sprintf(
                            "%s, %s, %s(%d)",
                            $grammar->wrap('metable_type'),
                            $grammar->wrap('key'),
                            $grammar->wrap('value'),
                            $stringIndexLength
                        ),
                        'value_string_prefix_index'
                    );
                } elseif (in_array($driver, ['pgsql', 'sqlite'])) {
                    $table->rawIndex(
                        sprintf(
                            "%s, %s, SUBSTR(%s, 1, %d)",
                            $grammar->wrap('metable_type'),
                            $grammar->wrap('key'),
                            $grammar->wrap('value'),
                            $stringIndexLength
                        ),
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
                && in_array(
                    $this->detectConnectionInUse()?->getDriverName(),
                    ['mysql', 'mariadb', 'pgsql', 'sqlite']
                )
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

    private function detectConnectionInUse(): ?Connection
    {
        /** @var \Illuminate\Database\Migrations\Migrator $migrator */
        $migrator = app('migrator');
        $repository = $migrator->getRepository();

        if (method_exists($repository, 'getConnectionResolver')) {
            $resolver = $repository->getConnectionResolver();
        } else {
            $resolver = DB::getFacadeRoot();
        }

        $connection = $resolver->connection(
            $this->getConnection() ?? $migrator->getConnection()
        );

        return $connection instanceof Connection ? $connection : null;
    }
};
