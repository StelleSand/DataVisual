<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChannelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //create 'channel' table
        Schema::create('channel', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->longText('detail');
            $table->integer('channel_number');
            $table->string('receiver_id');
            $table->double('nominal_power')->default(0);
            $table->double('power_factor')->default(1);
            $table->integer('create_user')->unsigned();
            $table->foreign('create_user')->references('id')->on('users');
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
        Schema::drop('channel');
    }
}
