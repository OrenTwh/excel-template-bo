<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBuyXGetYAdjustmentsToVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vouchers', function (Blueprint $table) {
            //
            $table->json('buy_x_get_y_adjustment')->nullable();
            $table->integer('total_claimable')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vouchers', function (Blueprint $table) {
            //
            $table->dropColumn('buy_x_get_y_adjustment');
            $table->dropColumn('total_claimable');
        });
    }
}
