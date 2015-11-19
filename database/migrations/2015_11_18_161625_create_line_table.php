<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLineTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //create 'line' table
        Schema::create('line', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->longText('detail');
            $table->longText('formula');
            $table->integer('create_user')->unsigned();
            $table->foreign('create_user')->references('id')->on('users');
            $table->dateTime('create_time');
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
        Schema::drop('line');
    }
}
