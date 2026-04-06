<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\{
    Validator,
};

use App\Services\{
    AnnouncementService,
    VoucherService
};

use App\Models\{
    Announcement,
    AnnouncementView
};

class AnnouncementController extends Controller
{
    /**
     * 1. Search Announcements
     * 
     * <aside class="notice">Get all announcements with filtering and pagination capabilities</aside>
     * 
     * @authenticated
     * 
     * @group Announcement API
     * 
     * @queryParam search string Search in announcement title and description. Example: promotion
     * @queryParam show_claimed integer To show claimed announcements (0=hide, 1=show). Example: 1
     * @queryParam discount_type integer Filter by discount type (1=percentage, 2=fixed_amount, 3=free_cup). Example: 1
     * @queryParam status integer Filter by status (10=active, 20=inactive). Example: 10
     * @queryParam page integer Page number for pagination. Example: 1
     * @queryParam per_page integer Items per page (max 50). Example: 10
     * 
     */
    public function searchAnnouncements(Request $request)
    {
        return AnnouncementService::searchAnnouncementsApi($request);
    }

    /**
     * 2. Get Announcements (Legacy)
     * 
     * <aside class="notice">Get all active announcements filtered, claim the promotion with claim voucher api</aside>
     * 
     * @authenticated
     * 
     * @group Announcement API
     * 
     * 
     */
    public function getAnnouncements(Request $request)
    {
        return AnnouncementService::getAnnouncements($request);
    }

    /**
     * 3. Get Announcement Details
     * 
     * <aside class="notice">Get detailed information about a specific announcement</aside>
     * 
     * @authenticated
     * 
     * @group Announcement API
     * 
     * @urlParam id required The ID of the announcement. Example: 1
     * 
     */
    public function getAnnouncement(Request $request, $id)
    {
        $request->merge(['id' => $id]);
        return AnnouncementService::getAnnouncementApi($request);
    }
}
