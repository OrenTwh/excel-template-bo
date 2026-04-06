<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sport_product_stock_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('variant_id');
            $table->integer('quantity_change')->comment('Positive = add, negative = deduct');
            $table->integer('quantity_after')->comment('Stock level after this change');
            $table->enum('type', ['restock', 'adjustment', 'sale', 'return', 'reserved', 'released']);
            $table->unsignedBigInteger('reference_id')->nullable()->comment('Order ID or other reference');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by_admin_id')->nullable();
            $table->timestamps();

            $table->foreign('variant_id')->references('id')->on('sport_product_variants')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sport_product_stock_logs');
    }
};
