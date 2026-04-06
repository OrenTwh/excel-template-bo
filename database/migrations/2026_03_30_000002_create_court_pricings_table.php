<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('court_pricings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_id')->constrained('courts')->onDelete('cascade');
            $table->string('label')->nullable();            // e.g. "Peak", "Off-Peak", "Group Deal"
            $table->time('time_from')->nullable();          // null = applies all day
            $table->time('time_to')->nullable();
            $table->unsignedTinyInteger('min_courts')->default(1); // min courts booked in one group booking
            $table->decimal('price', 10, 2);               // price per slot for this tier
            $table->timestamps();

            $table->index('court_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('court_pricings');
    }
};
