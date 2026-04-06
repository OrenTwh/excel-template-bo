<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIconToSportsTagsTable extends Migration
{
    public function up()
    {
        Schema::table( 'sports_tags', function ( Blueprint $table ) {
            $table->string( 'icon' )->nullable()->after( 'slug' );
        } );
    }

    public function down()
    {
        Schema::table( 'sports_tags', function ( Blueprint $table ) {
            $table->dropColumn( 'icon' );
        } );
    }
}
