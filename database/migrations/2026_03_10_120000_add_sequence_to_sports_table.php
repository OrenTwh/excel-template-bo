<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSequenceToSportsTable extends Migration
{
    public function up()
    {
        Schema::table('sports', function (Blueprint $table) {
            $table->unsignedInteger('sequence')->default(0)->after('active');
        });
    }

    public function down()
    {
        Schema::table('sports', function (Blueprint $table) {
            $table->dropColumn('sequence');
        });
    }
}
