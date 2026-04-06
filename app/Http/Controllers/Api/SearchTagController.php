<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\{
    SearchTag,
};

class SearchTagController extends Controller
{
    public function __construct() {}

    /**
     * 1. Get All Search Tags
     *
     * <aside class="notice">Get all active search tags</aside>
     *
     * @group Search Tag API
     *
     */
    public function getSearchTags(Request $request)
    {
        try {
            $searchTags = SearchTag::where('status', 10)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($searchTag) {
                    return [
                        'id' => $searchTag->id,
                        'title' => $searchTag->title,
                        'translations' => $searchTag->translations,
                        'icon' => $searchTag->icon,
                        'icon_path' => $searchTag->icon_path,
                        'created_at' => $searchTag->created_at,
                        'updated_at' => $searchTag->updated_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Search tags retrieved successfully',
                'data' => $searchTags,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching search tags',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}