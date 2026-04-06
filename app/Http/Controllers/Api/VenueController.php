<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\VenueService;

class VenueController extends Controller
{
    /**
     * Get Venues
     *
     * @group Venue API
     *
     * @queryParam per_page    integer Number of records per page, default 20. Example: 20
     * @queryParam name        string  Filter by venue name. Example: Sports Arena
     * @queryParam address     string  Search text matched against address_1. Example: Jalan Ampang
     * @queryParam city        string  Filter by city. Example: Kuala Lumpur
     * @queryParam state       string  Filter by state. Example: Selangor
     * @queryParam postcode    string  Filter by postcode. Example: 50450
     * @queryParam sport_ids   string[] Array of encrypted sport IDs to filter venues that offer ANY of those sports. Example: ["E2","def456"]
     * @queryParam sport_id    string  Single encrypted sport ID (legacy, use sport_ids instead). Example: E2
     * @queryParam lat         number  Latitude for distance sorting. Example: 3.1390
     * @queryParam lng         number  Longitude for distance sorting. Example: 101.6869
     *
     */
    public function getVenues(Request $request)
    {
        try {
            $venues = VenueService::getVenues($request);

            return response()->json([
                'status' => 'success',
                'data'   => $venues,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to retrieve venues',
            ], 500);
        }
    }

    /**
     * Get Venue Details
     *
     * @group Venue API
     *
     * @queryParam id string required Encrypted venue ID. Example: E2
     *
     */
    public function oneVenue(Request $request)
    {
        try {
            $venue = VenueService::getOneVenue($request);

            if (!$venue) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Venue not found',
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data'   => $venue,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to retrieve venue',
            ], 500);
        }
    }

    /**
     * Get Venue Sports (schedule & pricing configs)
     *
     * Returns VenueSport configurations for a venue, optionally filtered by sport.
     * Each record includes operating hours, slot duration, and price per slot.
     *
     * @group Venue API
     *
     * @queryParam venue_id string required Encrypted venue ID. Example: E2
     * @queryParam sport_id string Encrypted sport ID to filter. Example: E2
     *
     */
    public function getVenueSports(Request $request)
    {
        try {
            $venueSports = VenueService::getVenueSports($request);

            return response()->json([
                'status' => 'success',
                'data'   => $venueSports,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to retrieve venue sports',
            ], 500);
        }
    }
}
