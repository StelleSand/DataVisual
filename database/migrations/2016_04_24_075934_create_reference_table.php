<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReferenceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('reference', function (Blueprint $table) {
            $table->increments('id');
            $table->string('start');
            $table->string('end');
            $table->enum('attribute',array('MB','B','F','N'));
            $table->double('value');
            $table->double('powercost');
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
        Schema::drop('reference');
    }
}
