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
            $table->string('regex')->after('listvalues');
            $table->string('width')->after('regex');
            $table->boolean('capitals')->after('regex');
            $table->boolean('positive')->after('capitals');
            $table->text('appear_when')->after('positive');
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
