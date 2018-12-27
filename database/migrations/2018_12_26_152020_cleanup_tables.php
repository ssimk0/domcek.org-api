<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->dropColumn('avatar_file_size');
            $table->dropColumn('avatar_content_type');
            $table->dropColumn('avatar_updated_at');
            $table->boolean('is_admin')->default(false);
            $table->boolean('is_writer')->default(false);
            $table->boolean('is_registration')->default(false);
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
