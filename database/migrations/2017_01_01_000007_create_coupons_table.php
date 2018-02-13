<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('type')->defualt('coupon');
            $table->string('code');
            $table->longText('desc');
            $table->integer('flat_discount')->nullable();
            $table->integer('percentage_discount')->nullable();
            $table->timestamp('valid_from');
            $table->timestamp('valid_until')->nullable();
            $table->timestamps();
        });

        Schema::create('cart_coupon', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cart_id');
            $table->integer('coupon_id');
            $table->integer('user_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('cart_coupon');
    }
}
