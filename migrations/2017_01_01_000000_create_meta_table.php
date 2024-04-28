<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (!Schema::hasTable('meta')) {
            Schema::create('meta', function (Blueprint $table) {
                $table->id();
                $table->morphs('metable');
                $table->string('type')->nullable();
                $table->string('key')->index();
                $table->longtext('value');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('meta');
    }
};
