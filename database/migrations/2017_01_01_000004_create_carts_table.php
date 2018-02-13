<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

// 2017_01_01_000001_modify_users_table_add_useful_traits
class CreateCartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('amount'); // in cents
            $table->integer('discount')->nullable(); // in cents
            $table->integer('tax')->nullable(); // in cents
            $table->timestamps();
        });

        Schema::create('cart_product', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cart_id');
            $table->integer('product_id');
            $table->integer('quantity');
            $table->integer('unit_price');
            $table->string('details')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('carts');
        Schema::dropIfExists('cart_product');
    }
}
