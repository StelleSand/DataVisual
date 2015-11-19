<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSensorLineDeploymentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //create 'sensor_line_deployment' table
        Schema::create('sensor_line_deployment', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sensor_line_id')->unsigned();
            $table->foreign('sensor_line_id')->references('id')->on('sensor_line');
            $table->longText('formula');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
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
        Schema::drop('sensor_line_deployment');
    }
}
