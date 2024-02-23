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
             if (!Schema::hasColumn('pages', 'has_breadcrumb')) {
            Schema::table('pages', function (Blueprint $table) {
                $table->integer('has_breadcrumb')->default(1)->after('status')->nullable();
                $table->text('extra_config')->default('')->after('has_breadcrumb')->nullable();
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
        Schema::dropColumns('pages', ['has_breadcrumb','extra_config']);
    }
};
