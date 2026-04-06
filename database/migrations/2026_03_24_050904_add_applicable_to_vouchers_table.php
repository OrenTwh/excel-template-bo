<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApplicableToVouchersTable extends Migration
{
    /**
     * Add `applicable_to` to vouchers.
     *
     * Values:
     *   all     – redeemable on both court bookings and sport product orders
     *   booking – court bookings only
     *   product – sport product orders only
     */
    public function up()
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->string('applicable_to', 20)->default('all')
                ->comment('all | booking | product')
                ->after('type');
        });
    }

    public function down()
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn('applicable_to');
        });
    }
}
