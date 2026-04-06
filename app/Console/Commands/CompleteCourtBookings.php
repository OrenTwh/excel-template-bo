<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CourtBooking;
use Carbon\Carbon;

class CompleteCourtBookings extends Command
{
    protected $signature = 'court-booking:complete-passed';

    protected $description = 'Mark court bookings as complete once their booking date and end time have passed';

    public function handle()
    {
        $this->info('Checking for passed court bookings to complete...');

        $now     = Carbon::now();
        $today   = $now->format('Y-m-d');
        $nowTime = $now->format('H:i:s');

        $completed = CourtBooking::where('status', CourtBooking::STATUS_UPCOMING)
            ->where(function ($q) use ($today, $nowTime) {
                // Booking date is in the past
                $q->where('booking_date', '<', $today)
                  // Or booking date is today and end time has passed
                  ->orWhere(function ($q2) use ($today, $nowTime) {
                      $q2->where('booking_date', $today)
                         ->where('end_time', '<=', $nowTime);
                  });
            })
            ->get();

        $count = 0;

        foreach ($completed as $booking) {
            try {
                $booking->update(['status' => CourtBooking::STATUS_COMPLETE]);
                $count++;
                $this->line("Completed booking ID {$booking->id} (#{$booking->booking_no}) — {$booking->booking_date} {$booking->end_time}");
            } catch (\Exception $e) {
                $this->error("Error completing booking ID {$booking->id}: " . $e->getMessage());
            }
        }

        $this->info("Marked {$count} court booking(s) as complete.");

        return 0;
    }
}
