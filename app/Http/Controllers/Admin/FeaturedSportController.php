<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\FeaturedSportService;
use App\Models\Sport;

class FeaturedSportController extends Controller
{
    public function index(Request $request)
    {
        $this->data['header']['title'] = __('Featured Sports');
        $this->data['content']         = 'admin.featured_sport.index';
        $this->data['breadcrumb']      = [
            ['url' => route('admin.dashboard'), 'text' => __('template.dashboard'), 'class' => ''],
            ['url' => '', 'text' => __('Featured Sports'), 'class' => 'active'],
        ];
        $this->data['data']['status'] = [
            '10' => __('datatables.activated'),
            '20' => __('datatables.suspended'),
        ];
        $this->data['data']['sports'] = Sport::where('status', 10)
            
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.main')->with($this->data);
    }

    public function allFeaturedSports(Request $request)
    {
        return FeaturedSportService::allFeaturedSports($request);
    }

    public function createFeaturedSport(Request $request)
    {
        return FeaturedSportService::createFeaturedSport($request);
    }

    public function updateFeaturedSportStatus(Request $request)
    {
        return FeaturedSportService::updateFeaturedSportStatus($request);
    }

    public function updateSequence(Request $request)
    {
        return FeaturedSportService::updateSequence($request);
    }

    public function reorder(Request $request)
    {
        return FeaturedSportService::reorder($request);
    }

    public function deleteFeaturedSport(Request $request)
    {
        return FeaturedSportService::deleteFeaturedSport($request);
    }
}
