<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UseUuidAsPrimaryKeyForForms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('form_fields', function (Blueprint $table) {
            $table->dropForeign('formfields_form_id_foreign');
        });

        Schema::table('form_school', function (Blueprint $table) {
            $table->dropForeign(['form_id']);
        });

        Schema::table('form_school_category', function (Blueprint $table) {
            $table->dropForeign(['form_id']);
        });

        Schema::table('forms', function (Blueprint $table) {
            $table->uuid('id')->change();
        });

        Schema::table('form_fields', function (Blueprint $table) {
            $table->uuid('form_id')->change();
            $table->foreign('form_id')
                ->references('id')
                ->on('forms');
        });

        Schema::table('form_school', function (Blueprint $table) {
            $table->uuid('form_id')->change();
            $table->foreign('form_id')
                ->references('id')
                ->on('forms');
        });

        Schema::table('form_school_category', function (Blueprint $table) {
            $table->uuid('form_id')->change();
            $table->foreign('form_id')
                ->references('id')
                ->on('forms');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('form_fields', function (Blueprint $table) {
            $table->dropForeign(['form_id']);
        });

        Schema::table('form_school', function (Blueprint $table) {
            $table->dropForeign(['form_id']);
        });

        Schema::table('form_school_category', function (Blueprint $table) {
            $table->dropForeign(['form_id']);
        });

        Schema::table('forms', function (Blueprint $table) {
            $table->id('id')->change();
        });

        Schema::table('form_fields', function (Blueprint $table) {
            $table->id('form_id')->change();
            $table->foreign('form_id', 'formfields_form_id_foreign')
                ->references('id')
                ->on('forms');
        });

        Schema::table('form_school', function (Blueprint $table) {
            $table->id('form_id')->change();
            $table->foreign('form_id')
                ->references('id')
                ->on('forms');
        });

        Schema::table('form_school_category', function (Blueprint $table) {
            $table->id('form_id')->change();
            $table->foreign('form_id')
                ->references('id')
                ->on('forms');
        });
    }
}
