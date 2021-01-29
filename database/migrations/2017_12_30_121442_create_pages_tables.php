<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePagesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('parent_id')->nullable();
            $table->string('title', 200)->index();
            $table->string('slug', 200)->index();
            $table->longText('body')->nullable();
            $table->integer('order')->default(99);
            $table->boolean('active')->index();
            $table->timestamps();

            $table->unique(['parent_id', 'slug']);
            $table->foreign('parent_id')->references('id')->on('pages')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('pages');
    }
}
