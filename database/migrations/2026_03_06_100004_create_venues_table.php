<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVenuesTable extends Migration
{
    public function up()
    {
        Schema::create( 'venues', function ( Blueprint $table ) {
            $table->id();
            $table->string( 'name' );
            $table->string( 'slug' )->unique();
            $table->text( 'description' )->nullable();
            $table->string( 'address_1' )->nullable();
            $table->string( 'address_2' )->nullable();
            $table->string( 'city' )->nullable();
            $table->string( 'state' )->nullable();
            $table->string( 'postcode', 10 )->nullable();
            $table->decimal( 'latitude', 10, 7 )->nullable();
            $table->decimal( 'longitude', 10, 7 )->nullable();
            $table->string( 'image' )->nullable();
            $table->tinyInteger( 'status' )->default( 10 )->comment( '10=active, 20=inactive' );
            $table->timestamps();
        } );
    }

    public function down()
    {
        Schema::dropIfExists( 'venues' );
    }
}
