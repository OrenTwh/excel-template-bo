<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVoucherIdToUserVisitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->foreignId( 'voucher_id' )
            ->nullable()
            ->after( 'user_id' )
            ->constrained('vouchers')
            ->onDelete( 'set null' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table( 'user_visits', function ( Blueprint $table ) {
            $table->dropForeign( [ 'voucher_id' ] );
            $table->dropColumn( 'voucher_id' );
        } );
    }
}
