<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    SportService,
};

use App\Models\{
    Sport,
};

class SportController extends Controller
{
    public function index(Request $request)
    {
        $this->data['header']['title'] = __('Sports');
        $this->data['content'] = 'admin.sport.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route('admin.dashboard'),
                'text' => __('template.dashboard'),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __('Sports'),
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
        $this->data['header']['title'] = __('template.add_x', ['title' => 'Sport']);
        $this->data['content'] = 'admin.sport.add';
        $this->data['data']['type_options']           = Sport::typeOptions();
        $this->data['data']['pricing_method_options'] = Sport::pricingMethodOptions();
        $this->data['breadcrumb'] = [
            [
                'url' => route('admin.dashboard'),
                'text' => __('template.dashboard'),
                'class' => '',
            ],
            [
                'url' => route('admin.module_parent.sport.index'),
                'text' => __('Sports'),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __('template.add_x', ['title' => 'Sport']),
                'class' => 'active',
            ],
        ];

        return view('admin.main')->with($this->data);
    }

    public function edit(Request $request)
    {
        $this->data['header']['title'] = __('template.edit_x', ['title' => 'Sport']);
        $this->data['content'] = 'admin.sport.edit';
        $this->data['data']['type_options']           = Sport::typeOptions();
        $this->data['data']['pricing_method_options'] = Sport::pricingMethodOptions();
        $this->data['breadcrumb'] = [
            [
                'url' => route('admin.dashboard'),
                'text' => __('template.dashboard'),
                'class' => '',
            ],
            [
                'url' => route('admin.module_parent.sport.index'),
                'text' => __('Sports'),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __('template.edit_x', ['title' => 'Sport']),
                'class' => 'active',
            ],
        ];

        return view('admin.main')->with($this->data);
    }

    public function allSports(Request $request)
    {
        return SportService::allSports($request);
    }

    public function oneSport(Request $request)
    {
        return SportService::oneSport($request);
    }

    public function createSport(Request $request)
    {
        return SportService::createSport($request);
    }

    public function updateSport(Request $request)
    {
        return SportService::updateSport($request);
    }

    public function updateSportStatus(Request $request)
    {
        return SportService::updateSportStatus($request);
    }

    public function updateSequence(Request $request)
    {
        return SportService::updateSequence($request);
    }

    public function reorder(Request $request)
    {
        return SportService::reorder($request);
    }

    public function deleteSport(Request $request)
    {
        return SportService::deleteSport($request);
    }

    public function removeIconImage(Request $request)
    {
        return SportService::removeIconImage($request);
    }

    public function removeImage(Request $request)
    {
        return SportService::removeImage($request);
    }

    public function removeThumbnail(Request $request)
    {
        return SportService::removeThumbnail($request);
    }
}
