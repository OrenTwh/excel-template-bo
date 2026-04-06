<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sport_product_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sport_product_id');
            $table->string('name')->comment('Display name, e.g. Size M / Red');
            $table->string('sku')->unique()->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('compare_price', 10, 2)->nullable()->comment('Original/crossed-out price');
            $table->json('specs')->nullable()->comment('Key-value pairs e.g. {"Size":"M","Color":"Red"}');
            $table->string('image')->nullable()->comment('Variant-specific image');
            $table->integer('sequence')->default(0);
            $table->tinyInteger('status')->default(10)->comment('10=active, 20=inactive');
            $table->timestamps();

            $table->foreign('sport_product_id')->references('id')->on('sport_products')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sport_product_variants');
    }
};
