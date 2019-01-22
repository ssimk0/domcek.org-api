<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CleanupSliderImages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('slider_images', function (Blueprint $table) {
            $table->dropColumn('image_file_name');
            $table->boolean('active')->default(true);
        });

        Schema::table('slider_images', function (Blueprint $table) {
            $table->dropColumn('image_updated_at');
        });

        Schema::table('slider_images', function (Blueprint $table) {
            $table->dropColumn('image_content_type');
        });

        Schema::table('slider_images', function (Blueprint $table) {
            $table->dropColumn('image_file_size');
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