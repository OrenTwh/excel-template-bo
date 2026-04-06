<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGiftedFromToUserVouchersTable extends Migration
{
    public function up()
    {
        Schema::table( 'user_vouchers', function ( Blueprint $table ) {
            $table->unsignedBigInteger( 'gifted_from_user_id' )->nullable()->after( 'secret_code' );
            $table->foreign( 'gifted_from_user_id' )->references( 'id' )->on( 'users' )->onDelete( 'set null' );
        } );
    }

    public function down()
    {
        Schema::table( 'user_vouchers', function ( Blueprint $table ) {
            $table->dropForeign( [ 'gifted_from_user_id' ] );
            $table->dropColumn( 'gifted_from_user_id' );
        } );
    }
}
