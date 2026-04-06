<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SportProductOrderService;

class SportProductOrderController extends Controller
{
    public function index(Request $request)
    {
        $this->data['header']['title'] = __('Sport Product Orders');
        $this->data['content']         = 'admin.sport_product_order.index';
        $this->data['breadcrumb']      = [
            ['url' => route('admin.dashboard'), 'text' => __('template.dashboard'), 'class' => ''],
            ['url' => '', 'text' => __('Sport Product Orders'), 'class' => 'active'],
        ];
        $this->data['data'] = [
            'status_labels' => SportProductOrderService::statusLabels(),
        ];

        return view('admin.main')->with($this->data);
    }

    public function view(Request $request)
    {
        $this->data['header']['title'] = __('View Order');
        $this->data['content']         = 'admin.sport_product_order.view';
        $this->data['breadcrumb']      = [
            ['url' => route('admin.dashboard'), 'text' => __('template.dashboard'), 'class' => ''],
            ['url' => route('admin.module_parent.sport_product_order.index'), 'text' => __('Sport Product Orders'), 'class' => ''],
            ['url' => '', 'text' => __('View Order'), 'class' => 'active'],
        ];
        $this->data['data'] = [
            'status_labels' => SportProductOrderService::statusLabels(),
        ];

        return view('admin.main')->with($this->data);
    }

    // ── AJAX ──────────────────────────────────────────────────────────────────
    public function allOrders(Request $request)
    {
        return SportProductOrderService::allOrders($request);
    }

    public function oneOrder(Request $request)
    {
        return SportProductOrderService::oneOrder($request);
    }

    public function updateOrderStatus(Request $request)
    {
        return SportProductOrderService::updateOrderStatus($request);
    }
}
