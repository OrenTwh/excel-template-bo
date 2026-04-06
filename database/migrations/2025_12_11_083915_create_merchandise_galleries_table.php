<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMerchandiseGalleriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('merchandise_galleries', function (Blueprint $table) {
            $table->id();
            $table->foreignId( 'merchandise_id' )
                ->constrained()
                ->onDelete( 'cascade' );
            $table->string( 'image' );    // image path
            $table->tinyInteger( 'status' )->default( 10 );
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
        Schema::dropIfExists('merchandise_galleries');
    }
}
