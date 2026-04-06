<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentGatewayFieldsToCourtBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('court_bookings', function (Blueprint $table) {
            $table->string('payment_gateway')->nullable()->after('payment_status');
            $table->string('payment_gateway_ref')->nullable()->after('payment_gateway');
            $table->unsignedTinyInteger('payment_attempt')->default(0)->after('payment_gateway_ref');
        });
    }

    public function down()
    {
        Schema::table('court_bookings', function (Blueprint $table) {
            $table->dropColumn(['payment_gateway', 'payment_gateway_ref', 'payment_attempt']);
        });
    }
}
