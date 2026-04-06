<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeaturedSportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('featured_sports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sport_id');
            $table->unsignedInteger('sequence')->default(0);
            $table->tinyInteger('status')->default(10);
            $table->timestamps();

            $table->foreign('sport_id')->references('id')->on('sports')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('featured_sports');
    }
}
