<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\{
    Validator,
};

use App\Services\{
    BookingService,
};

use App\Models\{
    Booking,
};

class BookingController extends Controller
{
    public function __construct() {}

    /**
     * 1. Create Booking
     *
     * <aside class="notice">Create a new property booking for users with multiple property unit selection support</aside>
     *
     * <strong>booking_type</strong><br>
     * 1: Buy<br>
     * 2: Long Rent<br>
     * 3: Short Rent<br>
     * 
     * @authenticated
     *
     * @group Booking API
     *
     * @bodyParam property_id array required Array of property IDs to book (all must belong to the selected project). Example: [1, 2, 3]
     * @bodyParam property_id.* integer required Individual property ID. Example: 5
     * @bodyParam booking_date date required The preferred booking date (must be today or future date). Example: 2024-01-15
     * @bodyParam preferred_time string required The preferred time slot (09:00, 10:00, 11:00, 12:00, 13:00, 14:00, 15:00, 16:00, 17:00, 18:00). Example: 14:00
     * @bodyParam fullname string optional Full name of the person booking (defaults to authenticated user's name). Example: John Doe
     * @bodyParam email string optional Email address. Example: john@example.com
     * @bodyParam phone_number string optional Phone number. Example: 0123456789
     * @bodyParam calling_code string optional Country calling code (defaults to +60). Example: +60
     * @bodyParam bedrooms integer optional Number of bedrooms interested in (1-99). Example: 3
     * @bodyParam custom_messages string optional Additional messages or requirements (max 1000 characters). Example: Looking for family-friendly area
     * @bodyParam booking_type integer optional type of booking, default will be 1, Example: 1
     * 
     *
     */
    public function createBooking(Request $request)
    {
        return BookingService::createBookingApi($request);
    }

    /**
     * 2. Get User Bookings
     * 
     * <aside class="notice">Get all bookings for the authenticated user</aside>
     * 
     * <strong>booking_type</strong><br>
     * 1: Buy<br>
     * 2: Long Rent<br>
     * 3: Short Rent<br>
     * 
     * @authenticated
     * 
     * @group Booking API
     * 
     * @queryParam status integer Filter by booking status (10=active, 11=completed, 20=cancelled). Example: 10
     * @queryParam page integer Page number for pagination. Example: 1
     * @queryParam per_page integer Items per page (max 50). Example: 10
     * @queryParam booking_type integer optional type of booking, default will be 1, Example: 1
     * 
     * 
     */
    public function getUserBookings(Request $request)
    {
        return BookingService::getUserBookingsApi($request);
    }

    /**
     * 3. Get Booking Details
     * 
     * <aside class="notice">Get detailed information about a specific booking</aside>
     * 
     * @authenticated
     * 
     * @group Booking API
     * 
     * @urlParam id required The ID of the booking. Example: 1
     * 
     */
    public function getBooking(Request $request, $id)
    {
        $request->merge(['id' => $id]);
        return BookingService::getBookingApi($request);
    }

    /**
     * 4. Update Booking
     * 
     * <aside class="notice">Update an existing booking (only if not completed)</aside>
     * 
     * @authenticated
     * 
     * @group Booking API
     * 
     * @urlParam id required The ID of the booking to update. Example: 1
     * @bodyParam booking_date date optional The preferred booking date. Example: 2024-01-15
     * @bodyParam preferred_time string optional The preferred time slot. Example: 14:00
     * @bodyParam fullname string optional Full name of the person booking. Example: John Doe
     * @bodyParam email string required Email address. Example: john@example.com
     * @bodyParam phone_number string required Phone number. Example: 0123456789
     * @bodyParam calling_code string optional Country calling code. Example: +60
     * @bodyParam bedrooms integer optional Number of bedrooms interested in. Example: 3
     * @bodyParam custom_messages string optional Additional messages or requirements. Example: Looking for family-friendly area
     * 
     */
    public function updateBooking(Request $request, $id)
    {
        $request->merge(['id' => $id]);
        return BookingService::updateBookingApi($request);
    }

    /**
     * 5. Cancel Booking
     * 
     * <aside class="notice">Cancel a booking (soft delete by changing status)</aside>
     * 
     * @authenticated
     * 
     * @group Booking API
     * 
     * @urlParam id required The ID of the booking to cancel. Example: 1
     * 
     */
    public function cancelBooking(Request $request, $id)
    {
        $request->merge(['id' => $id]);
        return BookingService::cancelBookingApi($request);
    }

    /**
     * 6. Get Available Time Slots
     * 
     * <aside class="notice">Get available time slots for a property or specific property unit on a specific date</aside>
     * 
     * @group Booking API
     * 
     * @queryParam property_id integer required The ID of the property. Example: 1
     * @queryParam booking_date date required The date to check availability. Example: 2024-01-15
     * @queryParam booking_id integer optional Exclude this booking ID when checking availability (for updates). Example: 5
     * 
     */
    public function getAvailableTimeSlots(Request $request)
    {
        return BookingService::getAvailableTimeSlotsApi($request);
    }

    /**
     * 7. Get Time Slot Options
     * 
     * <aside class="notice">Get all available time slot options (9 AM to 6 PM)</aside>
     * 
     * @group Booking API
     * 
     */
    public function getTimeSlotOptions(Request $request)
    {
        return BookingService::getTimeSlotOptionsApi($request);
    }
}