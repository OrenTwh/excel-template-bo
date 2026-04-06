<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\{SportProductService, SportProductStockService};
use App\Models\{SportProductCategory, Sport};

class SportProductController extends Controller
{
    public function index(Request $request)
    {
        $this->data['header']['title'] = __('sport_product.products');
        $this->data['content']         = 'admin.sport_product.index';
        $this->data['breadcrumb'] = [
            ['url' => route('admin.dashboard'),  'text' => __('template.dashboard'),   'class' => ''],
            ['url' => '',                         'text' => __('sport_product.products'), 'class' => 'active'],
        ];
        $this->data['data']['status'] = [
            '10' => __('datatables.activated'),
            '20' => __('datatables.suspended'),
        ];
        $this->data['data']['categories'] = SportProductCategory::where('status', 10)
            ->with('children:id,parent_id,name')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get(['id', 'name']);
        $this->data['data']['sports'] = Sport::where('status', 10)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.main')->with($this->data);
    }

    public function add(Request $request)
    {
        $this->data['header']['title'] = __('template.add_x', ['title' => __('sport_product.product')]);
        $this->data['content']         = 'admin.sport_product.add';
        $this->data['breadcrumb'] = [
            ['url' => route('admin.dashboard'),                            'text' => __('template.dashboard'),   'class' => ''],
            ['url' => route('admin.module_parent.sport_product.index'),    'text' => __('sport_product.products'), 'class' => ''],
            ['url' => '',                                                   'text' => __('template.add_x', ['title' => __('sport_product.product')]), 'class' => 'active'],
        ];
        $this->data['data']['categories'] = SportProductCategory::where('status', 10)
            ->with('children:id,parent_id,name')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get(['id', 'name']);
        $this->data['data']['sports'] = Sport::where('status', 10)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.main')->with($this->data);
    }

    public function edit(Request $request)
    {
        $this->data['header']['title'] = __('template.edit_x', ['title' => __('sport_product.product')]);
        $this->data['content']         = 'admin.sport_product.edit';
        $this->data['breadcrumb'] = [
            ['url' => route('admin.dashboard'),                            'text' => __('template.dashboard'),   'class' => ''],
            ['url' => route('admin.module_parent.sport_product.index'),    'text' => __('sport_product.products'), 'class' => ''],
            ['url' => '',                                                   'text' => __('template.edit_x', ['title' => __('sport_product.product')]), 'class' => 'active'],
        ];
        $this->data['data']['categories'] = SportProductCategory::where('status', 10)
            ->with('children:id,parent_id,name')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get(['id', 'name']);
        $this->data['data']['sports'] = Sport::where('status', 10)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.main')->with($this->data);
    }

    public function allProducts(Request $request)      { return SportProductService::allProducts($request); }
    public function oneProduct(Request $request)       { return SportProductService::oneProduct($request); }
    public function createProduct(Request $request)    { return SportProductService::createProduct($request); }
    public function updateProduct(Request $request)    { return SportProductService::updateProduct($request); }
    public function updateProductStatus(Request $request) { return SportProductService::updateProductStatus($request); }
    public function removeProductImage(Request $request)  { return SportProductService::removeProductImage($request); }
    public function deleteProduct(Request $request)    { return SportProductService::deleteProduct($request); }

    public function createVariant(Request $request)      { return SportProductService::createVariant($request); }
    public function updateVariant(Request $request)      { return SportProductService::updateVariant($request); }
    public function deleteVariant(Request $request)      { return SportProductService::deleteVariant($request); }
    public function removeVariantImage(Request $request) { return SportProductService::removeVariantImage($request); }

    public function adjustStock(Request $request)      { return SportProductStockService::adjustStock($request); }
    public function getStockLogs(Request $request)     { return SportProductStockService::getStockLogs($request); }
    public function getVariantStock(Request $request)  { return SportProductStockService::getVariantStock($request); }
}
