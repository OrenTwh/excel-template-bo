<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sport_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->json('name');
            $table->string('slug')->unique();
            $table->json('description')->nullable();
            $table->json('images')->nullable()->comment('Array of image paths');
            $table->integer('sequence')->default(0);
            $table->tinyInteger('status')->default(10)->comment('10=active, 20=inactive');
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('sport_product_categories')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sport_products');
    }
};
