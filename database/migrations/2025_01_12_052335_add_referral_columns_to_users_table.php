<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReferralColumnsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->foreignId('referral_id')->nullable()->constrained('users')->onUpdate('restrict')->onDelete('cascade');
            $table->string( 'invitation_code', '6' )->nullable();
            $table->text( 'referral_structure' )->nullable();
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
            //
            $table->dropForeign(['referral_id']);
            $table->dropColumn('referral_id');
            $table->dropColumn('invitation_code');
            $table->dropColumn('referral_structure');
        });
    }
}
