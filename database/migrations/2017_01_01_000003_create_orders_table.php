<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

// 2017_01_01_000001_modify_users_table_add_useful_traits
class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('conekta_order')->nullable();
            $table->string('currency', 10)->defualt('MXN');
            $table->integer('amount');
            $table->integer('tax')->nullable();
            $table->integer('shipping_cost')->nullable();
            $table->integer('discount')->nullable();
            $table->integer('monthly_installments')->nullable(); // 3, 6, 9 and 12
            $table->enum(
                'payment_method',
                ['default', 'card', 'oxxo_cash', 'spei']
            )->default('default');
            $table->string('status');
            $table->string('tracking_number')->nullable();
            $table->timestamp('estimated_delivery')->nullable();
            $table->timestamps();
        });

        Schema::create('order_product', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_id');
            $table->morphs('orderable');
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
        Schema::dropIfExists('orders');
        Schema::dropIfExists('order_product');
    }
}
