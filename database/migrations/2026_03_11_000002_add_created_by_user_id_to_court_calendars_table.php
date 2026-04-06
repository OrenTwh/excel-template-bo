<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreatedByUserIdToCourtCalendarsTable extends Migration
{
    public function up()
    {
        Schema::table('court_calendars', function (Blueprint $table) {
            // null  = admin-created event
            // set   = user-created activity
            $table->foreignId('created_by_user_id')
                  ->nullable()
                  ->after('status')
                  ->constrained('users')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('court_calendars', function (Blueprint $table) {
            $table->dropForeign(['created_by_user_id']);
            $table->dropColumn('created_by_user_id');
        });
    }
}
