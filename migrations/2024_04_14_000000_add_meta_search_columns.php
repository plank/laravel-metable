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
            $table->string(
                'string_value',
                config('metable.stringValueIndexLength', 255)
            )->nullable();
            $table->string('hmac', 64)->nullable();
            $table->dropIndex(['key', 'metable_type']);
            $table->dropIndex(['key']);
            $table->index(['key', 'metable_type', 'numeric_value']);
            $table->index(['key', 'metable_type', 'string_value']);
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
            $table->dropIndex(['key', 'metable_type', 'string_value']);
            $table->dropIndex(['key', 'metable_type', 'numeric_value']);
            $table->index(['key']);
            $table->index(['key', 'metable_type']);
            $table->dropColumn('string_value');
            $table->dropColumn('numeric_value');
        });
    }
}
