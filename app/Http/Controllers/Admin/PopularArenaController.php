<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PopularArenaService;
use App\Models\Venue;

class PopularArenaController extends Controller
{
    public function index(Request $request)
    {
        $this->data['header']['title'] = __('Popular Arenas');
        $this->data['content']         = 'admin.popular_arena.index';
        $this->data['breadcrumb']      = [
            ['url' => route('admin.dashboard'), 'text' => __('template.dashboard'), 'class' => ''],
            ['url' => '', 'text' => __('Popular Arenas'), 'class' => 'active'],
        ];
        $this->data['data']['status'] = [
            '10' => __('datatables.activated'),
            '20' => __('datatables.suspended'),
        ];
        $this->data['data']['venues'] = Venue::where('status', 10)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.main')->with($this->data);
    }

    public function allPopularArenas(Request $request)
    {
        return PopularArenaService::allPopularArenas($request);
    }

    public function createPopularArena(Request $request)
    {
        return PopularArenaService::createPopularArena($request);
    }

    public function updatePopularArenaStatus(Request $request)
    {
        return PopularArenaService::updatePopularArenaStatus($request);
    }

    public function updateSequence(Request $request)
    {
        return PopularArenaService::updateSequence($request);
    }

    public function reorder(Request $request)
    {
        return PopularArenaService::reorder($request);
    }

    public function deletePopularArena(Request $request)
    {
        return PopularArenaService::deletePopularArena($request);
    }
}
