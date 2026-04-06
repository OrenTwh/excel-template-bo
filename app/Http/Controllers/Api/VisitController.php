<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\VisitService;

class VisitController extends Controller
{
    public function __construct() {}

    /**
     * Get User's Visits
     *
     * @group Visit API
     *
     * @queryParam status integer optional Filter by status (10=Active, 20=Inactive). Example: 10
     *
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthenticated'
                ], 401);
            }

            $visits = VisitService::getUserVisits($request, $user);

            return response()->json([
                'status' => 'success',
                'data' => $visits
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve visits',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create User Visit
     *
     * @group Visit API
     *
     * @bodyParam visit_date date required The visit date (Y-m-d format). Example: 2025-12-20
     * @bodyParam details array required Array of ticket details. Example: [{"ticket_type_id": "E2", "quantity": 2}]
     * @bodyParam details.*.ticket_type_id string required Encrypted ticket type ID. Example: E2
     * @bodyParam details.*.quantity integer required Ticket quantity. Example: 2
     *
     */
    public function store(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthenticated'
                ], 401);
            }

            $result = VisitService::createUserVisit($request, $user);

            // Check if result is a response (error response)
            if ($result instanceof \Illuminate\Http\Response || $result instanceof \Illuminate\Http\JsonResponse) {
                return $result;
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Visit created successfully',
                'data' => $result
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create visit',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Visit Details
     *
     * @group Visit API
     *
     * @urlParam id string required The encrypted visit ID. Example: E2
     * @queryParam include_qr integer optional Include QR codes (1 or 0). Example: 1
     *
     */
    public function show(Request $request, $id)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthenticated'
                ], 401);
            }
            // Get visit with QR codes
            $qrResult = VisitService::getVisitQR($id, $user, true);

            if (!$qrResult['success']) {
                return response()->json([
                    'status' => 'error',
                    'message' => $qrResult['message']
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'visit' => $qrResult['visit_data'],
                    'qr_codes' => $qrResult['qr_codes'],
                    'total_tickets' => count($qrResult['qr_codes']),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve visit',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check Availability
     *
     * @group Visit API
     *
     * @queryParam target_date string optional Target date to check (Y-m format for month or Y-m-d for specific date). If not provided, returns current month. Example: 2025-12 or 2025-12-20
     *
     */
    public function checkAvailability(Request $request)
    {
        try {
            $availability = VisitService::getAvailability($request);

            return response()->json([
                'status' => 'success',
                'data' => $availability
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to check availability',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Ticket Types
     *
     * @group Visit API
     *
     * @queryParam nationality string optional Filter by nationality. Example: local
     *
     */
    public function getTicketTypes(Request $request)
    {
        try {
            $ticketTypes = VisitService::getTicketTypes($request);

            return response()->json([
                'status' => 'success',
                'data' => $ticketTypes
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve ticket types',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
