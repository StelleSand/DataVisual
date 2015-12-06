<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //创建orders表
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('order_no');
            $table->integer('quantity');
            $table->double('price');
            $table->dateTime('create_date');
            $table->dateTime('print_date');
            $table->integer('merchandise_id')->unsigned();
            $table->foreign('merchandise_id')->references('id')->on('merchandise');
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
        Schema::drop('orders');
    }
}
