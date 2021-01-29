<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArchivesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archive_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug', 200)->unique();
            $table->string('title', 200);
            $table->enum('status', ['draft', 'published', 'archived']);
        });

        Schema::create('archives', function (Blueprint $table) {
            $table->increments('id');
            $table->text('name');
            $table->string('slug', 200)->unique();
            $table->enum('status', ['draft', 'published', 'archived']);
            $table->unsignedInteger('category_id');
            $table->foreign('category_id')->references('id')->on('archive_categories')->onDelete('cascade')->onUpdate('cascade');

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
        //Schema::dropIfExists('archives');
    }
}
