<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOnDeleteCascadeToFormSchool2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('form_school', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->foreign('school_id')
            ->references('id')
            ->on('schools')
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
            $table->dropForeign(['school_id']);
            $table->foreign('school_id')
            ->references('id')
            ->on('schools');
        });
    }
}
