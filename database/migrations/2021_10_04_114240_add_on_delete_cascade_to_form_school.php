<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOnDeleteCascadeToFormSchool extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('form_school', function (Blueprint $table) {
            $table->dropForeign(['form_id']);
            $table->foreign('form_id')
            ->references('id')
            ->on('forms')
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
        Schema::table('form_school', function (Blueprint $table) {
            $table->dropForeign(['form_id']);
            $table->foreign('form_id')
            ->references('id')
            ->on('forms');
        });
    }
}
