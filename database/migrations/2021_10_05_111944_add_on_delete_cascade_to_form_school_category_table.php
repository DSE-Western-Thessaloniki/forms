<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOnDeleteCascadeToFormSchoolCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('form_school_category', function (Blueprint $table) {
            $table->dropForeign(['form_id']);
            $table->foreign('form_id')
            ->references('id')
            ->on('forms')
            ->onDelete('cascade');
            $table->dropForeign(['school_category_id']);
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
        Schema::table('form_school_category', function (Blueprint $table) {
            $table->dropForeign(['form_id']);
            $table->foreign('form_id')
            ->references('id')
            ->on('forms');
            $table->dropForeign(['school_category_id']);
            $table->foreign('school_category_id')
            ->references('id')
            ->on('school_categories');
        });
    }
}
