<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourtCalendarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('court_calendars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_id')
                ->constrained('courts')
                ->onDelete('cascade');
            $table->date('date');
            $table->string('day_of_week'); // monday, tuesday, etc.
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_available')->default(true);
            $table->boolean('is_recurring')->default(false); // for weekly recurring schedules
            $table->string('unavailability_reason')->nullable();
            $table->decimal('special_price', 10, 2)->nullable(); // override price for special dates
            $table->tinyInteger('status')->default(10);
            $table->timestamps();

            $table->index(['court_id', 'date']);
            $table->index(['date', 'is_available']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('court_calendars');
    }
}
