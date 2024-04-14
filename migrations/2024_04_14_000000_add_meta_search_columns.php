<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
            $table->decimal('numeric_value', 18, 9)->nullable();
            $table->string('string_value', 255)->nullable();
            $table->dropIndex(['key', 'metable_type']);
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
            $table->index(['metable_type', 'metable_id']);
            $table->dropColumn('numeric_value');
            $table->dropColumn('string_value');
        });
    }
}
