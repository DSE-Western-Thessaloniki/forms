<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOnDeleteCascadeOnFormFields extends Migration
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
        Schema::table('form_fields', function (Blueprint $table) {
            $table->dropForeign(['form_id']);
            $table->foreign('form_id', 'formfields_form_id_foreign')
            ->references('id')
            ->on('forms');
        });
    }
}
