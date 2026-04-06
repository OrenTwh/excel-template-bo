<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSportIdToCourtsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('courts', function (Blueprint $table) {
            $table->foreignId('sport_id')
                ->nullable()
                ->after('id')
                ->constrained('sports')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('courts', function (Blueprint $table) {
            $table->dropForeign(['sport_id']);
            $table->dropColumn('sport_id');
        });
    }
}
