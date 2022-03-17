<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('order_menu', function (Blueprint $table){
            $table->id();
            $table->timestamps();
            $table->foreignId('order_id')->references('id')->on('orders');
            $table->foreignId('menu_id')->references('id')->on('menus');
            $table->integer('amount')->default(1);
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
};
