<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClaimableOptionsToVouchersTable extends Migration
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
            $table->tinyInteger('usable_amount')->nullable()->default(1);
            $table->integer('points_required')->nullable()->default(0);
            $table->decimal('min_spend',16,2)->nullable();
            $table->integer('min_order')->nullable()->default(0);

        });

        Schema::table('user_vouchers', function (Blueprint $table) {
            //
            $table->tinyInteger('total_used')->nullable()->default(0);
            $table->tinyInteger('total_left')->nullable()->default(0);
            $table->timestamp('used_at')->nullable();
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
            $table->dropColumn('usable_amount');
            $table->dropColumn('points_required');
            $table->dropColumn('min_spend');
            $table->dropColumn('min_order');
        });

        Schema::table('user_vouchers', function (Blueprint $table) {
            //
            $table->dropColumn('total_used');
            $table->dropColumn('total_left');
            $table->dropColumn('used_at');
        });
    }
}
