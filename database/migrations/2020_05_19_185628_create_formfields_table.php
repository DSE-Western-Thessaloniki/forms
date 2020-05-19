<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormfieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('formfields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained();
            $table->integer('sort_id');
            $table->string('title');
            $table->integer('type');
            $table->text('listvalues');
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
        Schema::dropIfExists('formfields');
    }
}
