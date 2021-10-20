<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormSchoolTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('form_school', function (Blueprint $table) {
            $table->id();
            $table->uuid('form_id');
            $table->foreign('form_id')
                ->references('id')
                ->on('forms');
            $table->bigInteger('school_id')
                ->unsigned();
            $table->foreign('school_id')
                ->references('id')
                ->on('schools');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('form_school');
    }
}
