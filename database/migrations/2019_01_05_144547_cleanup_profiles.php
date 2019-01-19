<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CleanupProfiles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('street');
        });
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('street_number');
        });
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('post_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
