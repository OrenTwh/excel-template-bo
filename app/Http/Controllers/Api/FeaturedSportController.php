<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\FeaturedSportService;

class FeaturedSportController extends Controller
{
    /**
     * Get Featured Sports
     *
     * @group Featured Sport API
     *
     * @queryParam per_page integer Number of records per page, default 20. Example: 20
     *
     */
    public function getFeaturedSports(Request $request)
    {
        try {
            $featuredSports = FeaturedSportService::getFeaturedSports($request);

            return response()->json([
                'status' => 'success',
                'data'   => $featuredSports,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to retrieve featured sports',
            ], 500);
        }
    }
}
