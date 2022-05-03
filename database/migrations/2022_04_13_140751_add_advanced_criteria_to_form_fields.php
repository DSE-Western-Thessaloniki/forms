<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdvancedCriteriaToFormFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('form_fields', function (Blueprint $table) {
            $table->string('regex')->default("")->after('listvalues');
            $table->string('width')->default("")->after('regex');
            $table->boolean('capitals')->default(false)->after('regex');
            $table->boolean('positive')->default(false)->after('capitals');
            $table->text('appear_when')->default("")->after('positive');
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
            $table->dropColumn('regex');
            $table->dropColumn('width');
            $table->dropColumn('capitals');
            $table->dropColumn('positive');
            $table->dropColumn('appear_when');
        });
    }
}
