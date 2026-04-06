<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\CourtService;

class CourtController extends Controller
{
    /**
     * Get Courts
     *
     * @group Court API
     *
     * @queryParam per_page integer Number of records per page, default 20. Example: 20
     * @queryParam name string Filter by court name. Example: court A
     * @queryParam venue_id string Encrypted venue ID to filter courts by venue. Example: E2
     * @queryParam sport_id string Encrypted sport ID to filter courts by sport. Example: E2
     *
     */
    public function getCourts(Request $request)
    {
        try {
            $courts = CourtService::getCourts($request);

            return response()->json([
                'status' => 'success',
                'data'   => $courts,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to retrieve courts',
            ], 500);
        }
    }

    /**
     * Get Court Details
     *
     * @group Court API
     *
     * @queryParam id string required Encrypted court ID. Example: E2
     *
     */
    public function oneCourt(Request $request)
    {
        try {
            $court = CourtService::getOneCourt($request);

            if (!$court) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Court not found',
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data'   => $court,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to retrieve court',
            ], 500);
        }
    }

    /**
     * Get Available Courts
     *
     * Returns courts available for booking at a venue for a given sport, date, start time, and duration.
     * Validates the requested window against VenueSport operating hours and filters out courts
     * that have conflicting active bookings.
     *
     * @group Court API
     *
     * @queryParam venue_id      string required Encrypted venue ID. Example: E2
     * @queryParam sport_id      string required Encrypted sport ID. Example: E2
     * @queryParam date          string required Booking date (Y-m-d). Example: 2026-03-10
     * @queryParam start_time    string required Desired start time (H:i). Example: 16:00
     * @queryParam duration      number required Duration in hours (can be decimal). Example: 1.5
     * @queryParam participants  integer Number of participants (default 1). Used for capacity-based courts. Example: 4
     *
     */
    public function getAvailableCourts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'venue_id'     => ['required'],
            'sport_id'     => ['required'],
            'date'         => ['required', 'date_format:Y-m-d'],
            'start_time'   => ['required', 'date_format:H:i'],
            'duration'     => ['required', 'numeric', 'min:0.5'],
            'participants' => ['nullable', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $result = CourtService::getAvailableCourts($request);

            if ($result['status'] === 'error') {
                return response()->json([
                    'status'  => 'error',
                    'message' => $result['message'],
                ], $result['code']);
            }

            return response()->json([
                'status' => 'success',
                'data'   => $result['data'],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to retrieve available courts',
            ], 500);
        }
    }

    /**
     * Get Available Dates
     *
     * Returns which dates in a given range have at least one bookable slot for the
     * selected venue + sport combination. Respects operating days and active bookings.
     *
     * @group Court API
     *
     * @queryParam venue_id      string required Encrypted venue ID. Example: E2
     * @queryParam sport_id      string required Encrypted sport ID. Example: E2
     * @queryParam month         string  Month to check (Y-m). Defaults to current month. Example: 2026-03
     * @queryParam date_from     string  Start date (Y-m-d), alternative to month. Example: 2026-03-01
     * @queryParam date_to       string  End date (Y-m-d). Example: 2026-03-31
     * @queryParam duration      number  Desired booking duration in hours (default = 1 slot). Example: 1.5
     * @queryParam participants  integer Number of participants (default 1). Example: 4
     *
     */
    public function getAvailableDates(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'venue_id'     => ['required'],
            'sport_id'     => ['required'],
            'month'        => ['nullable', 'date_format:Y-m'],
            'date_from'    => ['nullable', 'date_format:Y-m-d'],
            'date_to'      => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
            'duration'     => ['nullable', 'numeric', 'min:0.5'],
            'participants' => ['nullable', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $result = CourtService::getAvailableDates($request);

            if ($result['status'] === 'error') {
                return response()->json([
                    'status'  => 'error',
                    'message' => $result['message'],
                ], $result['code']);
            }

            return response()->json([
                'status' => 'success',
                'data'   => $result['data'],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to retrieve available dates',
            ], 500);
        }
    }

    /**
     * Get Available Times
     *
     * Returns all possible start-time slots for a given date and duration, with how many
     * courts are available per slot. Useful for building a time-picker after a date is chosen.
     *
     * @group Court API
     *
     * @queryParam venue_id      string required Encrypted venue ID. Example: E2
     * @queryParam sport_id      string required Encrypted sport ID. Example: E2
     * @queryParam date          string required Booking date (Y-m-d). Example: 2026-03-10
     * @queryParam duration      number Desired duration in hours (default = 1 slot). Example: 1.5
     * @queryParam participants  integer Number of participants (default 1). Example: 4
     *
     */
    public function getAvailableTimes(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'venue_id'     => ['required'],
            'sport_id'     => ['required'],
            'date'         => ['required', 'date_format:Y-m-d'],
            'duration'     => ['nullable', 'numeric', 'min:0.5'],
            'participants' => ['nullable', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $result = CourtService::getAvailableTimes($request);

            if ($result['status'] === 'error') {
                return response()->json([
                    'status'  => 'error',
                    'message' => $result['message'],
                ], $result['code']);
            }

            return response()->json([
                'status' => 'success',
                'data'   => $result['data'],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to retrieve available times',
            ], 500);
        }
    }
}
