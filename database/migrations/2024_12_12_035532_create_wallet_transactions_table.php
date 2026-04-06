<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_wallet_id')->constrained('wallets')->onUpdate( 'restrict')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onUpdate( 'restrict')->onDelete('cascade');
            $table->decimal('opening_balance',18,4);
            $table->decimal('amount',18,4);
            $table->decimal('closing_balance',18,4);
            $table->string('remark')->nullable();
            $table->tinyInteger('type');
            $table->tinyInteger('transaction_type');
            $table->tinyInteger('status')->default(10);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wallet_transactions');
    }
}
