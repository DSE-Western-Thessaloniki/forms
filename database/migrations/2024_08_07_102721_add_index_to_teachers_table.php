<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->index(['surname', 'name']);
            $table->index('name');
            $table->index('am');
            $table->index('afm');
        });

        Schema::table('other_teachers', function (Blueprint $table) {
            $table->index('name');
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropIndex(['surname', 'name']);
            $table->dropIndex(['name']);
            $table->dropIndex(['am']);
            $table->dropIndex(['afm']);
        });

        Schema::table('other_teachers', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['email']);
        });
    }
};
