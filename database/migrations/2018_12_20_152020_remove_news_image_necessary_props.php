<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveNewsImageNecessaryProps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('news_items', function (Blueprint $table) {
            $table->dropColumn('image_file_name');
        });

        Schema::table('news_items', function (Blueprint $table) {
            $table->dropColumn('image_file_size');
        });

        Schema::table('news_items', function (Blueprint $table) {
            $table->dropColumn('image_content_type');
        });

        Schema::table('news_items', function (Blueprint $table) {
            $table->dropColumn('image_updated_at');
        });

        Schema::drop('news_category_items');
        Schema::drop('news_categories');
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
