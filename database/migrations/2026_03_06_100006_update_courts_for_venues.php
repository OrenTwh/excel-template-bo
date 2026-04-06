<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCourtsForVenues extends Migration
{
    public function up()
    {
        Schema::table( 'courts', function ( Blueprint $table ) {
            // Remove old columns
            if ( Schema::hasColumn( 'courts', 'location_id' ) ) {
                $table->dropForeign( [ 'location_id' ] );
                $table->dropColumn( 'location_id' );
            }
            if ( Schema::hasColumn( 'courts', 'sport_id' ) ) {
                $table->dropForeign( [ 'sport_id' ] );
                $table->dropColumn( 'sport_id' );
            }
            if ( Schema::hasColumn( 'courts', 'location' ) ) {
                $table->dropColumn( 'location' );
            }

            // Add venue_sport_id
            $table->foreignId( 'venue_sport_id' )
                ->nullable()
                ->after( 'id' )
                ->constrained( 'venue_sports' )
                ->nullOnDelete();
        } );
    }

    public function down()
    {
        Schema::table( 'courts', function ( Blueprint $table ) {
            $table->dropForeign( [ 'venue_sport_id' ] );
            $table->dropColumn( 'venue_sport_id' );

            $table->unsignedBigInteger( 'sport_id' )->nullable()->after( 'id' );
            $table->unsignedBigInteger( 'location_id' )->nullable()->after( 'sport_id' );
            $table->string( 'location' )->nullable();
        } );
    }
}
