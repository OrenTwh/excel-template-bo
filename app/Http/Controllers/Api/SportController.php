<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SportService;

class SportController extends Controller
{
    /**
     * Get Sports
     *
     * @group Sport API
     *
     * @queryParam per_page integer Number of records per page, default 20. Example: 20
     * @queryParam name string Filter by sport name. Example: badminton
     *
     */
    public function getSports(Request $request)
    {
        try {
            $sports = SportService::getSports($request);

            return response()->json([
                'status' => 'success',
                'data'   => $sports,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to retrieve sports',
            ], 500);
        }
    }

    /**
     * Get Sport Details
     *
     * @group Sport API
     *
     * @queryParam id string required Encrypted sport ID. Example: E2
     *
     */
    public function oneSport(Request $request)
    {
        try {
            $sport = SportService::getOneSport($request);

            if (!$sport) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Sport not found',
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data'   => $sport,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to retrieve sport',
            ], 500);
        }
    }
}
