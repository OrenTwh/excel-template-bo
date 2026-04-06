<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourtGalleriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('court_galleries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_id')
                ->nullable()
                ->constrained('courts')
                ->onDelete('cascade');
            $table->string('image')->nullable();
            $table->tinyInteger( 'status' )->default(10);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('court_galleries');
    }
}
