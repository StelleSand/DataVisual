<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUnitTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //create 'unit' table
        Schema::create('unit', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->longText('detail');
            $table->longText('formula');
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
        Schema::drop('unit');
    }
}
