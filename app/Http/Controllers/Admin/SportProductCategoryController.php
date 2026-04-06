<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SportProductCategoryService;
use App\Models\SportProductCategory;

class SportProductCategoryController extends Controller
{
    public function index(Request $request)
    {
        $this->data['header']['title'] = __('sport_product.categories');
        $this->data['content']         = 'admin.sport_product_category.index';
        $this->data['breadcrumb'] = [
            ['url' => route('admin.dashboard'),                                'text' => __('template.dashboard'),        'class' => ''],
            ['url' => '',                                                       'text' => __('sport_product.categories'), 'class' => 'active'],
        ];
        $this->data['data']['status'] = [
            '10' => __('datatables.activated'),
            '20' => __('datatables.suspended'),
        ];
        $this->data['data']['parents'] = SportProductCategory::where('status', 10)
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.main')->with($this->data);
    }

    public function add(Request $request)
    {
        $this->data['header']['title'] = __('template.add_x', ['title' => __('sport_product.category')]);
        $this->data['content']         = 'admin.sport_product_category.add';
        $this->data['breadcrumb'] = [
            ['url' => route('admin.dashboard'),                                      'text' => __('template.dashboard'),        'class' => ''],
            ['url' => route('admin.module_parent.sport_product_category.index'),     'text' => __('sport_product.categories'), 'class' => ''],
            ['url' => '',                                                             'text' => __('template.add_x', ['title' => __('sport_product.category')]), 'class' => 'active'],
        ];
        $this->data['data']['parents'] = SportProductCategory::where('status', 10)
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.main')->with($this->data);
    }

    public function edit(Request $request)
    {
        $this->data['header']['title'] = __('template.edit_x', ['title' => __('sport_product.category')]);
        $this->data['content']         = 'admin.sport_product_category.edit';
        $this->data['breadcrumb'] = [
            ['url' => route('admin.dashboard'),                                      'text' => __('template.dashboard'),        'class' => ''],
            ['url' => route('admin.module_parent.sport_product_category.index'),     'text' => __('sport_product.categories'), 'class' => ''],
            ['url' => '',                                                             'text' => __('template.edit_x', ['title' => __('sport_product.category')]), 'class' => 'active'],
        ];
        $this->data['data']['parents'] = SportProductCategory::where('status', 10)
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.main')->with($this->data);
    }

    public function allCategories(Request $request)   { return SportProductCategoryService::allCategories($request); }
    public function oneCategory(Request $request)     { return SportProductCategoryService::oneCategory($request); }
    public function createCategory(Request $request)  { return SportProductCategoryService::createCategory($request); }
    public function updateCategory(Request $request)  { return SportProductCategoryService::updateCategory($request); }
    public function updateCategoryStatus(Request $request) { return SportProductCategoryService::updateCategoryStatus($request); }
    public function deleteCategory(Request $request)  { return SportProductCategoryService::deleteCategory($request); }
}
