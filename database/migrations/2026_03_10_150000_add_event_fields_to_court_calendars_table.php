<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEventFieldsToCourtCalendarsTable extends Migration
{
    public function up()
    {
        Schema::table('court_calendars', function (Blueprint $table) {
            $table->boolean('is_event')->default(false)->after('special_price');
            $table->string('event_title')->nullable()->after('is_event');
            $table->text('event_description')->nullable()->after('event_title');
            $table->unsignedInteger('max_participants')->nullable()->after('event_description');
            $table->decimal('price_per_participant', 10, 2)->nullable()->after('max_participants');
        });
    }

    public function down()
    {
        Schema::table('court_calendars', function (Blueprint $table) {
            $table->dropColumn([
                'is_event',
                'event_title',
                'event_description',
                'max_participants',
                'price_per_participant',
            ]);
        });
    }
}
