<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sport_product_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->json('name');
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->tinyInteger('sequence')->default(0);
            $table->tinyInteger('status')->default(10)->comment('10=active, 20=inactive');
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('sport_product_categories')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sport_product_categories');
    }
};
