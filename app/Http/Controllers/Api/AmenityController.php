<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\{
    Amenity,
};

class AmenityController extends Controller
{
    public function __construct() {}

    /**
     * 1. Get All Amenities
     *
     * <aside class="notice">Get all active amenities</aside>
     *
     * @group Amenity API
     *
     */
    public function getAmenities(Request $request)
    {
        try {
            $amenities = Amenity::where('status', 10)
                ->orderBy('title', 'asc')
                ->get()
                ->map(function ($amenity) {
                    return [
                        'id' => $amenity->id,
                        'title' => $amenity->title,
                        'icon' => $amenity->icon,
                        'icon_path' => $amenity->icon_path,
                        'created_at' => $amenity->created_at,
                        'updated_at' => $amenity->updated_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Amenities retrieved successfully',
                'data' => $amenities,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching amenities',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}