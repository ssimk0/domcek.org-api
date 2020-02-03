<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MoveGroupAnimatorToSeparateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('events_group', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('group_name');
            $table->unsignedInteger('animator')->nullable();
            $table->foreign('animator')->references('id')->on('users');
            $table->integer('event_id')->unsigned();
            $table->foreign('event_id')->references('id')->on('events');
            $table->timestamps();
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->dropForeign('groups_group_animator_foreign');
            $table->dropForeign('groups_event_id_foreign'); 
            $table->dropForeign('groups_participant_id_foreign');
            $table->dropColumn('group_animator');
            $table->dropColumn('event_id');
            $table->unsignedInteger('group_id')->nullable();            
            $table->foreign('group_id')->references('id')->on('events_group');
            $table->unique(['group_id', 'participant_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->unsignedInteger('group_animator')->nullable();
            $table->foreign('group_animator')->references('id')->on('users');
            $table->unsignedInteger('event_id')->nullable();
            $table->foreign('event_id')->references('id')->on('events');
        });

        Schema::dropIfExists('events_group');
    }
}
