<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddParticipantSubscribedColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->boolean('subscribed')->default(true);
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->timestamp('date_approved_term_and_condition')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn('subscribed');
        });
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('date_approved_term_and_condition');
        });
    }
}
