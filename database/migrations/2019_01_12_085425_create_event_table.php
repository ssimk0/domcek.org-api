<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->increments('id');
            $table->text('name');
            $table->text('theme')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->date('start_registration');
            $table->date('end_registration');
            $table->date('end_volunteer_registration');
            $table->integer('need_pay');
            $table->integer('deposit');

            $table->unique(['start_date', 'end_date']);
            $table->timestamps();
        });

        Schema::create('volunteer_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 120);
            $table->unique(['name', 'id']);
            $table->timestamps();
        });

        Schema::create('volunteers', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('is_leader')->default(false);
            $table->unsignedInteger('volunteer_type_id');
            $table->unsignedInteger('event_id')->index();
            $table->unsignedInteger('user_id')->index();


            $table->unique(['event_id', 'volunteer_type_id']);
            $table->foreign('volunteer_type_id')->references('id')->on('volunteer_types')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');

            $table->timestamps();
        });

        Schema::create('event_volunteer_types', function (Blueprint $table) {
            $table->unsignedInteger('volunteer_type_id');
            $table->unsignedInteger('event_id')->index();

            $table->primary(['event_id', 'volunteer_type_id']);

            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('volunteer_type_id')->references('id')->on('volunteer_types')->onDelete('cascade')->onUpdate('cascade');

            $table->timestamps();
        });


        Schema::create('participants', function (Blueprint $table) {
            $table->increments('id');
            $table->text('note');
            $table->unsignedInteger('event_id')->index();
            $table->unsignedInteger('user_id')->index();
            $table->unsignedInteger('register_by_user_id')->nullable();
            $table->unsignedInteger('changed_by_user_id')->nullable();
            $table->date('registration_date')->nullable();

            $table->unique(['event_id', 'user_id']);
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('register_by_user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('changed_by_user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');

            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->index();
            $table->bigInteger('payment_number')->index();
            $table->integer('paid');
            $table->integer('on_registration')->nullable();
            $table->integer('need_pay');
            $table->unsignedInteger('event_id');

            $table->primary(['user_id', 'event_id']);
            $table->unique(['payment_number']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade')->onUpdate('cascade');

            $table->timestamps();
        });

        Schema::create('groups', function (Blueprint $table) {
            $table->text('group_name');
            $table->unsignedInteger('group_animator');
            $table->unsignedInteger('event_id')->index();
            $table->unsignedInteger('participant_id')->index();

            $table->primary(['event_id', 'participant_id']);
            $table->foreign('participant_id')->references('id')->on('participants')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('group_animator')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');

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
    }
}
