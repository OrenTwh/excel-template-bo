<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnsToVenuesTable extends Migration
{
    public function up()
    {
        Schema::table( 'venues', function ( Blueprint $table ) {
            $table->text( 'about_us' )->nullable()->after( 'description' );
            $table->json( 'amenities' )->nullable()->after( 'about_us' );
            $table->json( 'opening_hours' )->nullable()->after( 'amenities' );
            $table->string( 'venue_layout' )->nullable()->after( 'opening_hours' );
            $table->text( 'venue_policy' )->nullable()->after( 'venue_layout' );
            $table->string( 'gmap_link' )->nullable()->after( 'venue_policy' );
            $table->string( 'waze_link' )->nullable()->after( 'gmap_link' );
            $table->string( 'calling_code', 10 )->nullable()->after( 'waze_link' );
            $table->string( 'phone_number', 20 )->nullable()->after( 'calling_code' );
            $table->string( 'whatsapp_link' )->nullable()->after( 'phone_number' );
        } );
    }

    public function down()
    {
        Schema::table( 'venues', function ( Blueprint $table ) {
            $table->dropColumn( [
                'about_us', 'amenities', 'opening_hours', 'venue_layout',
                'venue_policy', 'gmap_link', 'waze_link', 'calling_code',
                'phone_number', 'whatsapp_link',
            ] );
        } );
    }
}
