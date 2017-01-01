<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSampleClassesTables extends Migration
{
	public function up()
	{
		Schema::create('sample_metables', function (Blueprint $table) {
			$table->increments('id');
			$table->timestamps();

		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('sample_metables');
	}
}