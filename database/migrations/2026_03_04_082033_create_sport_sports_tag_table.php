<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSportSportsTagTable extends Migration
{
    public function up()
    {
        Schema::create('sport_sports_tag', function (Blueprint $table) {
            $table->foreignId('sport_id')->constrained('sports')->cascadeOnDelete();
            $table->foreignId('sports_tag_id')->constrained('sports_tags')->cascadeOnDelete();
            $table->primary(['sport_id', 'sports_tag_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('sport_sports_tag');
    }
}
