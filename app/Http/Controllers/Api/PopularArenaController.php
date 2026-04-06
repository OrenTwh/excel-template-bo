<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PopularArenaService;

class PopularArenaController extends Controller
{
    /**
     * Get Popular Arenas
     *
     * Returns active popular arenas ordered by sequence.
     * Each arena includes the venue details and the sports bound to it (with icons).
     *
     * @group Popular Arena API
     *
     * @queryParam per_page integer Number of records per page, default 20. Example: 20
     *
     */
    public function getPopularArenas(Request $request)
    {
        try {
            $popularArenas = PopularArenaService::getPopularArenas($request);

            return response()->json([
                'status' => 'success',
                'data'   => $popularArenas,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to retrieve popular arenas',
            ], 500);
        }
    }
}
