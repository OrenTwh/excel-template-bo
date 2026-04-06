<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CourtBooking;
use Carbon\Carbon;

class ExpirePendingCourtBookings extends Command
{
    protected $signature = 'court-booking:expire-pending';

    protected $description = 'Expire court bookings that have been pending payment for more than 10 minutes';

    public function handle()
    {
        $this->info('Checking for pending court bookings to expire...');

        $cutoff = Carbon::now()->subMinutes(10);

        $expired = CourtBooking::where('status', CourtBooking::STATUS_PENDING_PAYMENT)
            ->where('created_at', '<=', $cutoff)
            ->get();

        $count = 0;

        foreach ($expired as $booking) {
            try {
                $booking->update(['status' => CourtBooking::STATUS_SUSPENDED]);
                $count++;
                $this->line("Expired booking ID {$booking->id} (#{$booking->booking_no}) — created at {$booking->created_at}");
            } catch (\Exception $e) {
                $this->error("Error expiring booking ID {$booking->id}: " . $e->getMessage());
            }
        }

        $this->info("Expired {$count} pending court booking(s).");

        return 0;
    }
}
