<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVenueSportsTable extends Migration
{
    public function up()
    {
        Schema::create( 'venue_sports', function ( Blueprint $table ) {
            $table->id();
            $table->foreignId( 'venue_id' )->constrained( 'venues' )->cascadeOnDelete();
            $table->foreignId( 'sport_id' )->constrained( 'sports' )->cascadeOnDelete();
            $table->integer( 'slot_duration' )->default( 60 )->comment( 'minutes per slot' );
            $table->decimal( 'price_per_slot', 10, 2 )->default( 0 );
            $table->time( 'open_time' )->default( '08:00:00' );
            $table->time( 'close_time' )->default( '22:00:00' );
            $table->json( 'operating_days' )->nullable()->comment( '["monday","tuesday",...]' );
            $table->tinyInteger( 'status' )->default( 10 )->comment( '10=active, 20=inactive' );
            $table->timestamps();

            $table->unique( [ 'venue_id', 'sport_id' ] );
        } );
    }

    public function down()
    {
        Schema::dropIfExists( 'venue_sports' );
    }
}
