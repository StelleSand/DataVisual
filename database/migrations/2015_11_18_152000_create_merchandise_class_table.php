<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMerchandiseClassTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //create 'merchandise_class' table
        Schema::create('merchandise_class', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->longText('detail');
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
        Schema::drop('merchandise_class');
    }
}
