<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

// 2017_01_01_000001_modify_users_table_add_useful_traits
class CreateSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('name');
            $table->string('conekta_order_id')->nullable(); // last order id associated with the subscriptions
            $table->integer('period_amount')->default(1);
            $table->enum('period_unit', ['day', 'week', 'month', 'year'])->default('monthly');
            $table->integer('unit_price'); // in cents
            $table->integer('quantity')->default(1);
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->enum('status', ['active', 'paused', 'unpaid', 'cancelled']);
            $table->integer('rejected_payments')->default(0);
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
        Schema::dropIfExists('subscriptions');
    }
}
