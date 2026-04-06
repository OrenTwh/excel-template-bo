<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentFieldsToSportProductOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sport_product_orders', function (Blueprint $table) {
            $table->string('order_no')->nullable()->after('notes');
            $table->string('payment_gateway')->nullable()->after('order_no');
            $table->string('payment_gateway_ref')->nullable()->after('payment_gateway');
            $table->unsignedTinyInteger('payment_attempt')->default(0)->after('payment_gateway_ref');
        });
    }

    public function down()
    {
        Schema::table('sport_product_orders', function (Blueprint $table) {
            $table->dropColumn(['order_no', 'payment_gateway', 'payment_gateway_ref', 'payment_attempt']);
        });
    }
}
