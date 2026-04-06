<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sport_product_cart_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cart_id');
            $table->unsignedBigInteger('variant_id');
            $table->integer('quantity')->default(1);
            $table->timestamps();

            $table->unique(['cart_id', 'variant_id']);
            $table->foreign('cart_id')->references('id')->on('sport_product_carts')->onDelete('cascade');
            $table->foreign('variant_id')->references('id')->on('sport_product_variants')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sport_product_cart_items');
    }
};
