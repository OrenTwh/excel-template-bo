<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use Carbon\Carbon;

class CheckExpiredBooking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'booking:check-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for expired bookings and set status to 11';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Checking for expired bookings...');

        // Get bookings with active status (10) that have booking dates with times
        $bookings = Booking::with('property.project.country')
            ->where('status', 10)
            ->whereNotNull('booking_date')
            ->whereNotNull('preferred_time')
            ->get();

        $expiredCount = 0;

        foreach ($bookings as $booking) {
            try {
                // Get property timezone, fallback to app timezone
                $timezone = $booking->property->project->country->timezone ?? config('app.timezone');

                // Create booking datetime in property's timezone
                $bookingDateTime = Carbon::parse(
                    $booking->booking_date->format('Y-m-d') . ' ' . $booking->preferred_time,
                    $timezone
                );

                // Check if booking is passed (current time in property's timezone)
                $currentTime = Carbon::now($timezone);

                if ($bookingDateTime->isPast()) {
                    // Update status to 11 (expired/passed)
                    $booking->update(['status' => 11]);
                    $expiredCount++;

                    $this->line("Expired booking ID {$booking->id} - {$bookingDateTime->format('Y-m-d H:i')} ({$timezone})");
                }
            } catch (\Exception $e) {
                $this->error("Error processing booking ID {$booking->id}: " . $e->getMessage());
                continue;
            }
        }

        $this->info("Processed {$bookings->count()} active bookings.");
        $this->info("Updated {$expiredCount} expired bookings to status 11.");

        return 0;
    }
}
