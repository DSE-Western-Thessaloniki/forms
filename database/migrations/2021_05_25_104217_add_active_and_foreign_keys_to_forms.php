<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddActiveAndForeignKeysToForms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->boolean('active');
            $table->bigInteger('school_id')->unsigned();
            $table->foreign('school_id')
                ->references('id')
                ->on('schools');
            $table->bigInteger('school_category_id')->unsigned();
            $table->foreign('school_category_id')
                  ->references('id')
                  ->on('school_categories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropForeign(['school_category_id']);
            $table->dropColumn('school_id');
            $table->dropColumn('school_category_id');
            $table->dropColumn('active');
        });
    }
}
