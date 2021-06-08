<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormFieldDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('form_field_data', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('school_id')
                ->unsigned();
            $table->foreign('school_id')
                ->references('id')
                ->on('schools');
            $table->bigInteger('form_field_id')
                ->unsigned();
            $table->foreign('form_field_id')
                ->references('id')
                ->on('formfields');
            $table->text('data');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('form_field_data');
    }
}
