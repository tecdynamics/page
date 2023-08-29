<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
             if (!Schema::hasColumn('pages', 'is_restricted')) {
            Schema::table('pages', function (Blueprint $table) {
                $table->integer('is_restricted')->default(0)->after('extra_config')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropColumns('pages', ['is_restricted']);
    }
};
