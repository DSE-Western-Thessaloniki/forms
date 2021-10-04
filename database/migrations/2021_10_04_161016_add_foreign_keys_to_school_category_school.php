<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToSchoolCategorySchool extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('school_category_school', function (Blueprint $table) {
            $table->foreign('school_id')
                ->references('id')
                ->on('schools')
                ->onDelete('cascade');
            $table->foreign('school_category_id')
                ->references('id')
                ->on('school_categories')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('school_category_school', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropForeign(['school_category_id']);
        });
    }
}
