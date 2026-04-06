<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    LocationService,
};

use App\Models\{
    Location,
};

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $this->data['header']['title'] = __('Locations');
        $this->data['content'] = 'admin.location.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route('admin.dashboard'),
                'text' => __('template.dashboard'),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __('Locations'),
                'class' => 'active',
            ],
        ];
        $this->data['data']['status'] = [
            '10' => __('datatables.activated'),
            '20' => __('datatables.suspended'),
        ];
        $this->data['data']['types'] = [
            'state' => __('State'),
            'city' => __('City'),
            'area' => __('Area'),
            'district' => __('District'),
        ];
        $this->data['data']['parent_locations'] = Location::whereNull('parent_id')
            ->where('status', 10)
            
            ->get();

        return view('admin.main')->with($this->data);
    }

    public function add(Request $request)
    {
        $this->data['header']['title'] = __('template.add_x', ['title' => 'Location']);
        $this->data['content'] = 'admin.location.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route('admin.dashboard'),
                'text' => __('template.dashboard'),
                'class' => '',
            ],
            [
                'url' => route('admin.module_parent.location.index'),
                'text' => __('Locations'),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __('template.add_x', ['title' => 'Location']),
                'class' => 'active',
            ],
        ];
        $this->data['data']['types'] = [
            'state' => __('State'),
            'city' => __('City'),
            'area' => __('Area'),
            'district' => __('District'),
        ];
        $this->data['data']['parent_locations'] = Location::active()->get();

        return view('admin.main')->with($this->data);
    }

    public function edit(Request $request)
    {
        $this->data['header']['title'] = __('template.edit_x', ['title' => 'Location']);
        $this->data['content'] = 'admin.location.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route('admin.dashboard'),
                'text' => __('template.dashboard'),
                'class' => '',
            ],
            [
                'url' => route('admin.module_parent.location.index'),
                'text' => __('Locations'),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __('template.edit_x', ['title' => 'Location']),
                'class' => 'active',
            ],
        ];
        $this->data['data']['types'] = [
            'state' => __('State'),
            'city' => __('City'),
            'area' => __('Area'),
            'district' => __('District'),
        ];
        $this->data['data']['parent_locations'] = Location::active()->get();

        return view('admin.main')->with($this->data);
    }

    public function allLocations(Request $request)
    {
        return LocationService::allLocations($request);
    }

    public function oneLocation(Request $request)
    {
        return LocationService::oneLocation($request);
    }

    public function createLocation(Request $request)
    {
        return LocationService::createLocation($request);
    }

    public function updateLocation(Request $request)
    {
        return LocationService::updateLocation($request);
    }

    public function updateLocationStatus(Request $request)
    {
        return LocationService::updateLocationStatus($request);
    }

    public function deleteLocation(Request $request)
    {
        return LocationService::deleteLocation($request);
    }

    public function getLocationsByParent(Request $request)
    {
        return LocationService::getLocationsByParent($request);
    }

    public function getLocationHierarchy(Request $request)
    {
        return LocationService::getLocationHierarchy($request);
    }
}
