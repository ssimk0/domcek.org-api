<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventTransportTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transport_types', function (Blueprint $table) {
            $table->increments('id');
            $table->text('name');

            $table->timestamps();
        });

        Schema::create('event_transport_types', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('event_id')->index();
            $table->unsignedInteger('transport_type_id');
            $table->text('time')->nullable();

            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('transport_type_id')->references('id')->on('transport_types')->onDelete('cascade')->onUpdate('cascade');

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
        Schema::dropIfExists('event_transport_types');
        Schema::dropIfExists('transport_types');
    }
}
