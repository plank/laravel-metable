<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMetaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meta', function (Blueprint $table) {
            $table->increments('id');
            $table->string('metable_type');
            $table->unsignedInteger('metable_id');
            $table->string('type')->default('null');
            $table->string('key')->index();
            $table->longtext('value');

            $table->index(['metable_type', 'metable_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('meta');
    }
}
