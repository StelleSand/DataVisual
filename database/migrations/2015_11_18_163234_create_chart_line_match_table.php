<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChartLineMatchTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //create 'chart_line_match' table
        Schema::create('chart_line_match', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('chart_id')->unsigned();
            $table->foreign('chart_id')->references('id')->on('chart');
            $table->integer('line_id');
            $table->foreign('line_id')->references('id')->on('line');
            $table->timestamps();
            //此处line_id应该为sensor_line或者merchandise_line的外键，但是暂未考虑清楚实际使用方式，外键约束暂时留白
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
        Schema::drop('chart_line_match');
    }
}
