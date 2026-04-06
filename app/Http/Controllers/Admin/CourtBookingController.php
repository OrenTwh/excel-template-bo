<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    CourtBookingService,
};

use App\Models\{
    CourtBooking,
    Court,
    User,
    Venue,
};

class CourtBookingController extends Controller
{
    // ── Status-label map shared across all page methods ───────────────────────
    private function statusLabels(): array
    {
        return CourtBooking::statusLabels();
    }

    // ── Common page data ─────────────────────────────────────────────────────
    private function commonData(): array
    {
        return [
            'status_labels'  => $this->statusLabels(),
            'payment_status' => [
                'pending'  => __('Pending'),
                'paid'     => __('Paid'),
                'failed'   => __('Failed'),
                'refunded' => __('Refunded'),
            ],
            'courts' => Court::where('status', 10)
                ->with(['venueSport.venue:id,name', 'venueSport.sport:id,name'])
                ->get(),
        ];
    }

    // ── All bookings (unfiltered) ─────────────────────────────────────────────
    public function index(Request $request)
    {
        $this->data['header']['title'] = __('Court Bookings');
        $this->data['content']         = 'admin.court_booking.index';
        $this->data['breadcrumb']      = [
            ['url' => route('admin.dashboard'), 'text' => __('template.dashboard'), 'class' => ''],
            ['url' => '', 'text' => __('Court Bookings'), 'class' => 'active'],
        ];
        $this->data['data'] = array_merge($this->commonData(), [
            'section'       => 'all',
            'status_filter' => [],
        ]);

        return view('admin.main')->with($this->data);
    }

    // ── Upcoming bookings (status = 10) ───────────────────────────────────────
    public function upcomingIndex(Request $request)
    {
        $this->data['header']['title'] = __('Upcoming Bookings');
        $this->data['content']         = 'admin.court_booking.index';
        $this->data['breadcrumb']      = [
            ['url' => route('admin.dashboard'), 'text' => __('template.dashboard'), 'class' => ''],
            ['url' => '', 'text' => __('Upcoming Bookings'), 'class' => 'active'],
        ];
        $this->data['data'] = array_merge($this->commonData(), [
            'section'       => 'upcoming',
            'status_filter' => [CourtBooking::STATUS_UPCOMING],
        ]);

        return view('admin.main')->with($this->data);
    }

    // ── Upcoming bookings (status = 10) ───────────────────────────────────────
    public function pendingIndex(Request $request)
    {
        $this->data['header']['title'] = __('Pending Payment Bookings');
        $this->data['content']         = 'admin.court_booking.index';
        $this->data['breadcrumb']      = [
            ['url' => route('admin.dashboard'), 'text' => __('template.dashboard'), 'class' => ''],
            ['url' => '', 'text' => __('Pending Payment Bookings'), 'class' => 'active'],
        ];
        $this->data['data'] = array_merge($this->commonData(), [
            'section'       => 'pending',
            'status_filter' => [CourtBooking::STATUS_PENDING_PAYMENT],
        ]);

        return view('admin.main')->with($this->data);
    }

    // ── Complete bookings (status = 11) ───────────────────────────────────────
    public function completeIndex(Request $request)
    {
        $this->data['header']['title'] = __('Completed Bookings');
        $this->data['content']         = 'admin.court_booking.index';
        $this->data['breadcrumb']      = [
            ['url' => route('admin.dashboard'), 'text' => __('template.dashboard'), 'class' => ''],
            ['url' => '', 'text' => __('Completed Bookings'), 'class' => 'active'],
        ];
        $this->data['data'] = array_merge($this->commonData(), [
            'section'       => 'complete',
            'status_filter' => [CourtBooking::STATUS_COMPLETE],
        ]);

        return view('admin.main')->with($this->data);
    }

    // ── Suspended / Canceled bookings (status = 20 or 21) ────────────────────
    public function suspendedIndex(Request $request)
    {
        $this->data['header']['title'] = __('Suspended Bookings');
        $this->data['content']         = 'admin.court_booking.index';
        $this->data['breadcrumb']      = [
            ['url' => route('admin.dashboard'), 'text' => __('template.dashboard'), 'class' => ''],
            ['url' => '', 'text' => __('Suspended Bookings'), 'class' => 'active'],
        ];
        $this->data['data'] = array_merge($this->commonData(), [
            'section'       => 'suspended',
            'status_filter' => [CourtBooking::STATUS_SUSPENDED, CourtBooking::STATUS_CANCELED],
        ]);

        return view('admin.main')->with($this->data);
    }

    // ── Booking Calendar ──────────────────────────────────────────────────────
    public function bookingCalendar(Request $request)
    {
        $this->data['header']['title'] = __('Booking Calendar');
        $this->data['content']         = 'admin.court_booking.booking_calendar';
        $this->data['breadcrumb']      = [
            ['url' => route('admin.dashboard'), 'text' => __('template.dashboard'), 'class' => ''],
            ['url' => '', 'text' => __('Booking Calendar'), 'class' => 'active'],
        ];
        $this->data['data'] = [
            'status_labels' => $this->statusLabels(),
            'venues'        => Venue::where('status', 10)->orderBy('name')->get(['id', 'name']),
            'courts'        => Court::where('status', 10)
                ->with(['venueSport.venue:id,name', 'venueSport.sport:id,name'])
                ->orderBy('name')
                ->get(),
        ];

        return view('admin.main')->with($this->data);
    }

    // ── Add / Edit pages ──────────────────────────────────────────────────────
    public function add(Request $request)
    {
        $this->data['header']['title'] = __('template.add_x', ['title' => 'Court Booking']);
        $this->data['content']         = 'admin.court_booking.add';
        $this->data['breadcrumb']      = [
            ['url' => route('admin.dashboard'), 'text' => __('template.dashboard'), 'class' => ''],
            ['url' => route('admin.module_parent.court_booking.upcoming'), 'text' => __('Court Bookings'), 'class' => ''],
            ['url' => '', 'text' => __('template.add_x', ['title' => 'Court Booking']), 'class' => 'active'],
        ];
        $this->data['data'] = array_merge($this->commonData(), [
            'users'          => User::where('status', 10)->get(),
            'booking_status' => $this->statusLabels(),
        ]);

        return view('admin.main')->with($this->data);
    }

    public function edit(Request $request)
    {
        $this->data['header']['title'] = __('template.edit_x', ['title' => 'Court Booking']);
        $this->data['content']         = 'admin.court_booking.edit';
        $this->data['breadcrumb']      = [
            ['url' => route('admin.dashboard'), 'text' => __('template.dashboard'), 'class' => ''],
            ['url' => route('admin.module_parent.court_booking.upcoming'), 'text' => __('Court Bookings'), 'class' => ''],
            ['url' => '', 'text' => __('template.edit_x', ['title' => 'Court Booking']), 'class' => 'active'],
        ];
        $this->data['data'] = array_merge($this->commonData(), [
            'users'          => User::where('status', 10)->get(),
            'booking_status' => $this->statusLabels(),
        ]);

        return view('admin.main')->with($this->data);
    }

    public function view(Request $request)
    {
        $this->data['header']['title'] = __('View Court Booking');
        $this->data['content']         = 'admin.court_booking.view';
        $this->data['breadcrumb']      = [
            ['url' => route('admin.dashboard'), 'text' => __('template.dashboard'), 'class' => ''],
            ['url' => route('admin.module_parent.court_booking.upcoming'), 'text' => __('Court Bookings'), 'class' => ''],
            ['url' => '', 'text' => __('View'), 'class' => 'active'],
        ];

        return view('admin.main')->with($this->data);
    }

    // ── AJAX endpoints ────────────────────────────────────────────────────────
    public function allCourtBookings(Request $request)
    {
        return CourtBookingService::allCourtBookings($request);
    }

    public function oneCourtBooking(Request $request)
    {
        return CourtBookingService::oneCourtBooking($request);
    }

    public function createCourtBooking(Request $request)
    {
        return CourtBookingService::createCourtBooking($request);
    }

    public function updateCourtBooking(Request $request)
    {
        return CourtBookingService::updateCourtBooking($request);
    }

    public function updateBookingStatus(Request $request)
    {
        return CourtBookingService::updateBookingStatus($request);
    }

    public function updatePaymentStatus(Request $request)
    {
        return CourtBookingService::updatePaymentStatus($request);
    }

    public function cancelBooking(Request $request)
    {
        return CourtBookingService::cancelBooking($request);
    }

    public function confirmBooking(Request $request)
    {
        return CourtBookingService::confirmBooking($request);
    }

    public function deleteCourtBooking(Request $request)
    {
        return CourtBookingService::deleteCourtBooking($request);
    }

    public function checkAvailability(Request $request)
    {
        return CourtBookingService::checkAvailability($request);
    }

    public function getBookingsByDate(Request $request)
    {
        return CourtBookingService::getBookingsByDate($request);
    }

    // ── Availability Statistic ────────────────────────────────────────────────
    public function availabilityStatistic(Request $request)
    {
        $this->data['header']['title'] = __('Availability Statistics');
        $this->data['content']         = 'admin.court_booking.statistic';
        $this->data['breadcrumb']      = [
            ['url' => route('admin.dashboard'), 'text' => __('template.dashboard'), 'class' => ''],
            ['url' => '', 'text' => __('Availability Statistics'), 'class' => 'active'],
        ];
        $this->data['data'] = [
            'venues' => Venue::where('status', 10)->orderBy('name')->get(['id', 'name']),
            'courts' => Court::where('status', 10)
                ->with(['venueSport.venue:id,name'])
                ->orderBy('name')
                ->get(),
        ];

        return view('admin.main')->with($this->data);
    }

    public function getAvailabilityData(Request $request)
    {
        return CourtBookingService::getAvailabilityData($request);
    }
}
