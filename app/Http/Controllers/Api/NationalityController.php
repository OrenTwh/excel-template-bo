<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Nationality;

class NationalityController extends Controller
{
    /**
     * Get Nationalities
     * 
     * <aside class="notice">Get all active nationalities</aside>
     * 
     * @group Nationality API
     * 
     * @queryParam search string Search in nationality title. Example: Malaysian
     * @queryParam per_page integer Items per page (max 100). Example: 20
     * @queryParam page integer Page number for pagination. Example: 1
     * 
     */
    public function getNationalities(Request $request)
    {
        try {
            $query = Nationality::where('status', 10); // Only active nationalities
            
            // Search functionality
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where('title', 'LIKE', "%{$search}%");
            }
            
            // Order by title alphabetically
            // $query->orderBy('title', 'ASC');
            
            // Pagination
            $perPage = $request->input('per_page', 50);
            $perPage = min($perPage, 100); // Limit to 100 items per page max
            
            if ($request->has('per_page')) {
                $nationalities = $query->paginate($perPage);
                
                // Transform the data for API response
                $nationalities->getCollection()->transform(function ($nationality) {
                    return [
                        'id' => $nationality->id,
                        'name' => $nationality->name,
                        'symbol' => $nationality->symbol,
                        'image' => $nationality->image_path,
                        'status' => $nationality->status,
                        'created_at' => $nationality->created_at,
                        'updated_at' => $nationality->updated_at,
                    ];
                });
                
                return response()->json([
                    'success' => true,
                    'data' => $nationalities->items(),
                    'pagination' => [
                        'current_page' => $nationalities->currentPage(),
                        'last_page' => $nationalities->lastPage(),
                        'per_page' => $nationalities->perPage(),
                        'total' => $nationalities->total(),
                        'from' => $nationalities->firstItem(),
                        'to' => $nationalities->lastItem(),
                    ]
                ]);
            } else {
                // Return all results without pagination
                $nationalities = $query->get();
                
                $nationalityData = $nationalities->map(function ($nationality) {

                    return [
                        'id' => $nationality->id,
                        'name' => $nationality->name,
                        'symbol' => $nationality->symbol,
                        'image' => $nationality->image_path,
                        'status' => $nationality->status,
                        'created_at' => $nationality->created_at,
                        'updated_at' => $nationality->updated_at,
                    ];
                });
                
                return response()->json([
                    'success' => true,
                    'data' => $nationalityData,
                    'total' => $nationalities->count()
                ]);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching nationalities',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Nationality Details
     * 
     * <aside class="notice">Get detailed information about a specific nationality</aside>
     * 
     * @group Nationality API
     * 
     * @urlParam id required The ID of the nationality. Example: 1
     * 
     */
    public function getNationality(Request $request, $id)
    {
        try {
            $nationality = Nationality::where('id', $id)
                ->where('status', 10)
                ->first();

            if (!$nationality) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nationality not found or inactive'
                ], 404);
            }

            $nationalityData = [
                'id' => $nationality->id,
                'title' => $nationality->title,
                'icon' => $nationality->icon_path,
                'status' => $nationality->status,
                'created_at' => $nationality->created_at,
                'updated_at' => $nationality->updated_at,
            ];

            return response()->json([
                'success' => true,
                'data' => $nationalityData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching nationality details',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}