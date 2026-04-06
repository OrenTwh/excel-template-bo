<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sport_product_sport', function (Blueprint $table) {
            $table->unsignedBigInteger('sport_product_id');
            $table->unsignedBigInteger('sport_id');
            $table->primary(['sport_product_id', 'sport_id']);

            $table->foreign('sport_product_id')->references('id')->on('sport_products')->onDelete('cascade');
            $table->foreign('sport_id')->references('id')->on('sports')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sport_product_sport');
    }
};
