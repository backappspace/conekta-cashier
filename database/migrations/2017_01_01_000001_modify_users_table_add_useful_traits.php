<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

// 2017_01_01_000001_modify_users_table_add_useful_traits
class ModifyUsersTableAddUsefulTraits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('conekta_client_id')->nullable();
            $table->string('card_brand', 20)->nullable();
            $table->string('card_last_4', 4)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['conekta_client_id', 'card_brand', 'card_last_4']);
        });
    }
}
