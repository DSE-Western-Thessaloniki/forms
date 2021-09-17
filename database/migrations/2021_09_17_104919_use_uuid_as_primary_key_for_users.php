<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UseUuidAsPrimaryKeyForUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('user_role', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->uuid('id')->change();
        });

        Schema::table('forms', function (Blueprint $table) {
            $table->uuid('user_id')->change();
            $table->foreign('user_id')
                ->references('id')
                ->on('users');
        });

        Schema::table('user_role', function (Blueprint $table) {
            $table->uuid('user_id')->change();
            $table->foreign('user_id')
                ->references('id')
                ->on('users');
        });

        Schema::table('schools', function (Blueprint $table) {
            $table->uuid('updated_by')->change();
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
            $table->dropForeign(['user_id']);
        });

        Schema::table('user_role', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->id('id')->change();
        });

        Schema::table('forms', function (Blueprint $table) {
            $table->id('user_id')->change();
            $table->foreign('user_id')
                ->references('id')
                ->on('users');
        });

        Schema::table('user_role', function (Blueprint $table) {
            $table->id('user_id')->change();
            $table->foreign('user_id')
                ->references('id')
                ->on('users');
        });

        Schema::table('schools', function (Blueprint $table) {
            $table->id('updated_by')->change();
        });
    }
}
