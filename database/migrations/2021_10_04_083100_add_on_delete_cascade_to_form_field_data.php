<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOnDeleteCascadeToFormFieldData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('form_field_data', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->foreign('school_id')
            ->references('id')
            ->on('schools')
            ->onDelete('cascade');
            $table->dropForeign(['form_field_id']);
            $table->foreign('form_field_id')
            ->references('id')
            ->on('form_fields')
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
        Schema::table('form_field_data', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->foreign('school_id')
            ->references('id')
            ->on('schools');
            $table->dropForeign(['form_field_id']);
            $table->foreign('form_field_id')
            ->references('id')
            ->on('form_fields');
        });
    }
}
