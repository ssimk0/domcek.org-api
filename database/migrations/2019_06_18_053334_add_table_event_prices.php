<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddTableEventPrices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_prices', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('event_id')->index();
            $table->integer('need_pay')->default(true);
            $table->integer('deposit')->default(true);
            $table->string('description')->nullable();
            $table->timestamps();

            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade')->onUpdate('cascade');
        });

        $prices = DB::table('events')->get(['id', 'need_pay', 'deposit']);

        foreach ($prices as $price) {
            DB::table('event_prices')->insert([
                'event_id' => $price->id,
                'deposit' => $price->deposit,
                'need_pay'=> $price->need_pay,
            ]);
        }

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('deposit');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('need_pay');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedInteger('event_price_id')->nullable();
            $table->foreign('event_price_id')->references('id')->on('event_prices')->onDelete('cascade')->onUpdate('cascade');
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
