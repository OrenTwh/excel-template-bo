<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Services\CourtBookingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class CourtBookingController extends Controller
{
    /**
     * Get My Bookings
     *
     * Returns the authenticated user's court bookings, most recent first.
     *
     * @authenticated
     * @group Court Booking API
     *
     * @queryParam status    integer Filter by booking status (1=Pending Payment, 10=Upcoming, 11=Complete, 20=Suspended, 21=Canceled). Example: 10
     * @queryParam date_from string  Filter from date (Y-m-d). Example: 2026-03-01
     * @queryParam date_to   string  Filter to date (Y-m-d). Example: 2026-03-31
     * @queryParam per_page  integer Records per page, default 15. Example: 15
     *
     */
    public function getMyBookings(Request $request)
    {
        try {
            $bookings = CourtBookingService::getMyBookings($request);

            return response()->json(['status' => 'success', 'data' => $bookings]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to retrieve bookings', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get One Booking
     *
     * Returns full details of a single booking belonging to the authenticated user.
     *
     * @authenticated
     * @group Court Booking API
     *
     * @queryParam id string required Encrypted booking ID. Example: E2
     *
     */
    public function getOneBooking(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        try {
            $booking = CourtBookingService::getOneBooking($request);

            if (!$booking) {
                return response()->json(['status' => 'error', 'message' => 'Booking not found'], 404);
            }

            return response()->json(['status' => 'success', 'data' => $booking]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to retrieve booking', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Create Booking
     *
     * Creates one or more court bookings for the authenticated user.
     * Supports both single and multiple courts in a single request.
     * All bookings start in Pending Payment status.
     *
     * **Single court** (original format):
     * Send flat fields — `court_id`, `booking_date`, `start_time`, `end_time`.
     *
     * **Multiple courts** (array format):
     * Send indexed arrays — `court_id[0]`, `booking_date[0]`, `start_time[0]`, `end_time[0]`,
     * `court_id[1]`, `booking_date[1]`, etc.
     *
     * A single `voucher_code` applies to the combined grand total and the discount
     * is distributed proportionally across all created bookings.
     *
     * @authenticated
     * @group Court Booking API
     *
     * @bodyParam court_id      string|string[] required Encrypted court ID(s). Example: E2
     * @bodyParam booking_date  string|string[] required Booking date(s) (Y-m-d), today or future. Example: 2026-03-15
     * @bodyParam start_time    string|string[] required Start time(s) (H:i). Example: 16:00
     * @bodyParam end_time      string|string[] required End time(s) (H:i). Example: 17:30
     * @bodyParam participants  integer|integer[] Number of participants per court (default 1). Example: 4
     * @bodyParam notes         string|string[]  Optional notes per court. Example: Extra shuttlecocks please.
     * @bodyParam voucher_code  string  Promo/voucher code to apply to the entire order. Example: SAVE20
     *
     */
    public function createBooking(Request $request)
    {
        // ── Normalize single values to arrays for uniform handling ────────────
        $courtIds     = $request->input('court_id');
        $bookingDates = $request->input('booking_date');
        $startTimes   = $request->input('start_time');
        $endTimes     = $request->input('end_time');
        $participants = $request->input('participants');
        $notes        = $request->input('notes');

        $isMultiple = is_array($courtIds);

        if (!$isMultiple) {
            $courtIds     = [$courtIds];
            $bookingDates = [$bookingDates];
            $startTimes   = [$startTimes];
            $endTimes     = [$endTimes];
            $participants = [$participants];
            $notes        = [$notes];
        }

        // ── Validate ──────────────────────────────────────────────────────────
        $validator = Validator::make([
            'court_id'     => $courtIds,
            'booking_date' => $bookingDates,
            'start_time'   => $startTimes,
            'end_time'     => $endTimes,
            'participants' => $participants,
            'notes'        => $notes,
            'voucher_code' => $request->input('voucher_code'),
        ], [
            'court_id'       => ['required', 'array', 'min:1'],
            'court_id.*'     => ['required'],
            'booking_date'   => ['required', 'array'],
            'booking_date.*' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'start_time'     => ['required', 'array'],
            'start_time.*'   => ['required', 'date_format:H:i'],
            'end_time'       => ['required', 'array'],
            'end_time.*'     => ['required', 'date_format:H:i'],
            'participants'   => ['nullable', 'array'],
            'participants.*' => ['nullable', 'integer', 'min:1'],
            'notes'          => ['nullable', 'array'],
            'notes.*'        => ['nullable', 'string', 'max:500'],
            'voucher_code'   => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        // ── Validate end_time > start_time per entry ──────────────────────────
        foreach ($courtIds as $i => $courtId) {
            if (!Carbon::parse($endTimes[$i])->gt(Carbon::parse($startTimes[$i]))) {
                $label = $isMultiple ? ' for court #' . ($i + 1) : '';
                return response()->json([
                    'status'  => 'error',
                    'message' => "end_time must be after start_time{$label}",
                ], 422);
            }
        }

        // ── Merge normalized data back onto request for service ───────────────
        $request->merge([
            'court_id'     => $courtIds,
            'booking_date' => $bookingDates,
            'start_time'   => $startTimes,
            'end_time'     => $endTimes,
            'participants' => $participants,
            'notes'        => $notes,
        ]);

        try {
            $result = CourtBookingService::createBooking($request, $isMultiple);

            if ($result['status'] === 'error') {
                return response()->json(['status' => 'error', 'message' => $result['message']], $result['code']);
            }

            return response()->json([
                'status'  => 'success',
                'message' => $result['message'],
                'data'    => $result['data'],
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to create booking', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Cancel Booking
     *
     * Cancels a booking belonging to the authenticated user.
     * Only bookings in Pending Payment or Upcoming status can be cancelled.
     *
     * @authenticated
     * @group Court Booking API
     *
     * @bodyParam id                  string required Encrypted booking ID. Example: E2
     * @bodyParam cancellation_reason string required Reason for cancellation. Example: Change of plans.
     *
     */
    public function cancelBooking(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'                  => ['required'],
            'cancellation_reason' => ['required', 'string', 'max:500'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $result = CourtBookingService::cancelUserBooking($request);

            if ($result['status'] === 'error') {
                return response()->json(['status' => 'error', 'message' => $result['message']], $result['code']);
            }

            return response()->json([
                'status'  => 'success',
                'message' => $result['message'],
                'data'    => $result['data'],
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to cancel booking'], 500);
        }
    }

    /**
     * Download Booking Receipt (PDF)
     *
     * Streams a PDF receipt for the given booking. Only the booking owner can download.
     *
     * @authenticated
     * @group Court Booking API
     *
     * @queryParam id string required Encrypted booking ID. Example: E2
     *
     */
    public function getReceipt(Request $request)
    {
        try {
            $result = CourtBookingService::getReceiptData($request);

            if (!$result) {
                return response()->json(['status' => 'error', 'message' => 'Booking not found'], 404);
            }

            $pdf = Pdf::loadView('api.court_booking.receipt', [
                'group' => $result['group'],
            ])->setPaper('a4', 'portrait');

            return $pdf->download('Receipt_' . $result['group']->group_no . '.pdf');

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to generate receipt', 'error' => $e->getMessage()], 500);
        }
    }
}
