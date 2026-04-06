<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sport_product_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->tinyInteger('status')->default(10)->comment('10=pending, 20=confirmed, 30=completed, 40=cancelled');
            $table->unsignedBigInteger('voucher_id')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0)->comment('Before discount');
            $table->decimal('discount', 10, 2)->default(0)->comment('Voucher discount applied');
            $table->decimal('total', 10, 2)->default(0)->comment('After discount');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('voucher_id')->references('id')->on('vouchers')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sport_product_orders');
    }
};
