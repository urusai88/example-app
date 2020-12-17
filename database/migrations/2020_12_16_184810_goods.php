<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Goods extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goods_categories', function (Blueprint $blueprint) {
            $blueprint->integer('id')->autoIncrement();
            $blueprint->string('title');
        });

        Schema::create('goods', function (Blueprint $blueprint) {
            $blueprint->integer('id')->autoIncrement();
            $blueprint->string('title');
        });

        Schema::create('goods_2_goods_categories', function (Blueprint $blueprint) {
            $blueprint->integer('goods_id');
            $blueprint->integer('goods_categories_id');

            $blueprint->primary(['goods_id', 'goods_categories_id']);
            $blueprint->foreign('goods_id')->references('id')->on('goods')->cascadeOnDelete()->cascadeOnUpdate();
            $blueprint->foreign('goods_categories_id')->references('id')->on('goods_categories')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('goods_2_goods_categories');
        Schema::drop('goods');
        Schema::drop('goods_categories');
    }
}
