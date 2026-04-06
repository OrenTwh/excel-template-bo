<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSecretCodeToUserVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_vouchers', function (Blueprint $table) {
            $table->string( 'secret_code', '8' )->nullable();
        });

        Schema::table('vouchers', function (Blueprint $table) {
            $table->integer( 'claim_per_user' )->nullable()->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_vouchers', function (Blueprint $table) {
            $table->dropColumn('secret_code');
        });

        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn( 'claim_per_user' );
        });
    }
}
