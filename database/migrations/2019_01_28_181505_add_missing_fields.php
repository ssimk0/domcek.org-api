<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMissingFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('volunteer_types', function (Blueprint $table) {
            $table->boolean('active')->default(false);
        });

        Schema::table('event_transport_times', function (Blueprint $table) {
            $table->enum('type', ['out', 'in'])->default('in');
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
