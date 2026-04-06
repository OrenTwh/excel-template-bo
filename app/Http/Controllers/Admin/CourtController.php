<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    CourtService,
};

use App\Models\{
    Court,
    VenueSport,
    Location,
};

class CourtController extends Controller
{
    public function index(Request $request)
    {
        $this->data['header']['title'] = __('court.title_plural');
        $this->data['content'] = 'admin.court.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route('admin.dashboard'),
                'text' => __('template.dashboard'),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __('court.title_plural'),
                'class' => 'active',
            ],
        ];
        $this->data['data']['status'] = [
            '10' => __('datatables.activated'),
            '20' => __('datatables.suspended'),
        ];
        return view('admin.main')->with($this->data);
    }

    public function add(Request $request)
    {
        $this->data['header']['title'] = __('template.add_x', ['title' => __('court.title')]);
        $this->data['content'] = 'admin.court.add';
        $this->data['breadcrumb'] = [
            [ 'url' => route('admin.dashboard'), 'text' => __('template.dashboard'), 'class' => '' ],
            [ 'url' => route('admin.module_parent.court.index'), 'text' => __('court.title_plural'), 'class' => '' ],
            [ 'url' => '', 'text' => __('template.add_x', ['title' => __('court.title')]), 'class' => 'active' ],
        ];
        $this->data['data']['venue_sports'] = VenueSport::with('venue:id,name', 'sport:id,name')
            ->where('status', 10)->get();
        $this->data['data']['locations'] = Location::where('status', 10)->get();

        return view('admin.main')->with($this->data);
    }

    public function bulkAdd(Request $request)
    {
        $this->data['header']['title'] = 'Bulk Add Courts';
        $this->data['content'] = 'admin.court.bulk_add';
        $this->data['breadcrumb'] = [
            [ 'url' => route('admin.dashboard'), 'text' => __('template.dashboard'), 'class' => '' ],
            [ 'url' => route('admin.module_parent.court.index'), 'text' => __('Courts'), 'class' => '' ],
            [ 'url' => '', 'text' => 'Bulk Add', 'class' => 'active' ],
        ];
        $this->data['data']['venue_sports'] = VenueSport::with('venue:id,name', 'sport:id,name')
            ->where('status', 10)->get();

        return view('admin.main')->with($this->data);
    }

    public function bulkCreateCourts(Request $request)
    {
        return CourtService::bulkCreateCourts($request);
    }

    public function edit(Request $request)
    {
        $this->data['header']['title'] = __('template.edit_x', ['title' => __('court.title')]);
        $this->data['content'] = 'admin.court.edit';
        $this->data['breadcrumb'] = [
            [ 'url' => route('admin.dashboard'), 'text' => __('template.dashboard'), 'class' => '' ],
            [ 'url' => route('admin.module_parent.court.index'), 'text' => __('court.title_plural'), 'class' => '' ],
            [ 'url' => '', 'text' => __('template.edit_x', ['title' => __('court.title')]), 'class' => 'active' ],
        ];
        $this->data['data']['venue_sports'] = VenueSport::with('venue:id,name', 'sport:id,name')
            ->where('status', 10)->get();
        $this->data['data']['locations'] = Location::where('status', 10)->get();

        return view('admin.main')->with($this->data);
    }

    public function allCourts(Request $request)
    {
        return CourtService::allCourts($request);
    }

    public function oneCourt(Request $request)
    {
        return CourtService::oneCourt($request);
    }

    public function createCourt(Request $request)
    {
        return CourtService::createCourt($request);
    }

    public function updateCourt(Request $request)
    {
        return CourtService::updateCourt($request);
    }

    public function updateCourtStatus(Request $request)
    {
        return CourtService::updateCourtStatus($request);
    }

    public function deleteCourt(Request $request)
    {
        return CourtService::deleteCourt($request);
    }

    public function duplicateCourt(Request $request)
    {
        return CourtService::duplicateCourt($request);
    }

    public function removeCourtImage(Request $request)
    {
        return CourtService::removeCourtImage($request);
    }

    public function removeCourtGalleryImage(Request $request)
    {
        return CourtService::removeCourtGalleryImage($request);
    }
}
