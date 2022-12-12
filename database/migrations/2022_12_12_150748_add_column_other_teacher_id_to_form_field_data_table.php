<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('form_field_data', function (Blueprint $table) {
            $table->unsignedBigInteger('other_teacher_id')->nullable();
            $table->foreign('other_teacher_id')
                    ->references('id')
                    ->on('other_teachers');
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
            $table->dropForeign(['other_teacher_id']);
            $table->dropColumn('other_teacher_id');
        });
    }
};
