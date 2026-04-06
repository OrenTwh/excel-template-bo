<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourtBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('court_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_id')
                ->constrained('courts')
                ->onDelete('cascade');
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->string('booking_no')->unique();
            $table->date('booking_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('duration_hours')->default(1);
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('payment_status')->default('pending'); // pending, paid, failed, refunded
            $table->string('booking_status')->default('pending'); // pending, confirmed, cancelled, completed
            $table->text('notes')->nullable();
            $table->foreignId('voucher_id')->nullable()->constrained('vouchers')->onDelete('set null');
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->tinyInteger('status')->default(10);
            $table->timestamps();

            $table->index(['booking_date', 'start_time']);
            $table->index(['user_id', 'booking_date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('court_bookings');
    }
}
