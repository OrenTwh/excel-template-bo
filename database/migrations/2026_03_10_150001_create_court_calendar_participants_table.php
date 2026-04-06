<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourtCalendarParticipantsTable extends Migration
{
    public function up()
    {
        Schema::create('court_calendar_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_calendar_id')
                ->constrained('court_calendars')
                ->onDelete('cascade');
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->tinyInteger('status')->default(10); // 10=confirmed, 20=cancelled
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->timestamps();

            $table->unique(['court_calendar_id', 'user_id']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('court_calendar_participants');
    }
}
