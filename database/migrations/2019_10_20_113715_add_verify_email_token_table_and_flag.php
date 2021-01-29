<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVerifyEmailTokenTableAndFlag extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_registration')) {
                $table->dropColumn('is_registration');
            }
            $table->boolean('is_verified')->default(false);
        });

        Schema::create('verification_token', function (Blueprint $table) {
            $table->string('token', 32);
            $table->string('email');
            $table->dateTime('valid_until');
            $table->boolean('used')->default(false);
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
        Schema::dropIfExists('verification_token');
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_registration')->default(false);

            if (Schema::hasColumn('users', 'is_verified')) {
                $table->dropColumn('is_verified');
            }
        });
    }
}
