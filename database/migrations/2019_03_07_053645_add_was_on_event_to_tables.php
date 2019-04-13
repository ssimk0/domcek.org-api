<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWasOnEventToTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('volunteers', function (Blueprint $table) {
            $table->boolean('was_on_event')->default(false);
        });

        Schema::table('participants', function (Blueprint $table) {
            $table->boolean('was_on_event')->default(false);
            $table->dropColumn('registration_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('volunteers', function (Blueprint $table) {
            $table->dropColumn('was_on_event');
        });

        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn('was_on_event');
            $table->date('registration_date')->nullable();
        });
    }
}
