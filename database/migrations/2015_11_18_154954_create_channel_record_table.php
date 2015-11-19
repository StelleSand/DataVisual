<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChannelRecordTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //create 'channel_record' table
        Schema::create('channel_record', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('channel_id')->unsigned();
            $table->foreign('channel_id')->references('id')->on('channel');
            $table->enum('record_type',array('power','temperature'))->default('power');
            $table->dateTime('date');
            $table->double('value');
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
        Schema::drop('channel_record');
    }
}
