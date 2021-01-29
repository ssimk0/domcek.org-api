<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CleanupTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('permission_role');
        Schema::drop('role_user');
        Schema::drop('roles');
        Schema::drop('permission_user');
        Schema::drop('permissions');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('avatar_file_name');
            $table->boolean('is_admin')->default(false);
            $table->boolean('is_writer')->default(false);
            $table->boolean('is_registration')->default(false);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('avatar_file_size');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('avatar_content_type');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('avatar_updated_at');
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
