<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sport_product_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->integer('quantity');
            $table->string('image')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('sport_product_orders')->onDelete('cascade');
            $table->foreign('variant_id')->references('id')->on('sport_product_variants')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sport_product_order_items');
    }
};
