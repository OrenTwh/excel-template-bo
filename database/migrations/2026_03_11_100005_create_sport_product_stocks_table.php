<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sport_product_stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('variant_id')->unique();
            $table->integer('quantity')->default(0);
            $table->integer('reserved_quantity')->default(0)->comment('Held for pending orders');
            $table->timestamps();

            $table->foreign('variant_id')->references('id')->on('sport_product_variants')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sport_product_stocks');
    }
};
