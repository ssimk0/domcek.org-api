<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArchiveItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archive_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('archive_id');
            $table->enum('type', ['audio', 'video', 'image']);
            $table->text('name');
            $table->string('audio', 255)->nullable()->unique();
            $table->string('video', 255)->nullable()->unique();
            $table->string('image', 255)->nullable()->unique();
            $table->string('image_file_name')->nullable();
            $table->integer('image_file_size')->nullable();
            $table->string('image_content_type')->nullable();
            $table->timestamp('image_updated_at')->nullable();

            $table->foreign('archive_id')->references('id')->on('archives')->onDelete('cascade')->onUpdate('cascade');
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
//        Schema::dropIfExists('archive_items');
    }
}
