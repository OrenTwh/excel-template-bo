<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBookingProductToVoucherUsagesTable extends Migration
{
    /**
     * Link voucher usages to court bookings and sport product orders.
     *
     * Existing `order_id` (vending-machine orders) is left untouched.
     * New nullable FKs cover the two new order types.
     */
    public function up()
    {
        Schema::table('voucher_usages', function (Blueprint $table) {
            $table->unsignedBigInteger('court_booking_id')->nullable()->after('voucher_id');
            $table->unsignedBigInteger('sport_product_order_id')->nullable()->after('court_booking_id');

            $table->foreign('court_booking_id')
                ->references('id')->on('court_bookings')
                ->onUpdate('restrict')->onDelete('set null');

            $table->foreign('sport_product_order_id')
                ->references('id')->on('sport_product_orders')
                ->onUpdate('restrict')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('voucher_usages', function (Blueprint $table) {
            $table->dropForeign(['court_booking_id']);
            $table->dropForeign(['sport_product_order_id']);
            $table->dropColumn(['court_booking_id', 'sport_product_order_id']);
        });
    }
}
