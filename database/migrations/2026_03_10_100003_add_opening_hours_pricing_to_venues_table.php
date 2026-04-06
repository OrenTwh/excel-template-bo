<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOpeningHoursPricingToVenuesTable extends Migration
{
    public function up()
    {
        Schema::table( 'venues', function ( Blueprint $table ) {
            $table->longText( 'opening_hours_pricing' )->nullable()->after( 'opening_hours' );
        } );
    }

    public function down()
    {
        Schema::table( 'venues', function ( Blueprint $table ) {
            $table->dropColumn( 'opening_hours_pricing' );
        } );
    }
}
