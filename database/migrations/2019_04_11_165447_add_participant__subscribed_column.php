<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->timestamp('date_approved_term_and_condition')->useCurrent();
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
