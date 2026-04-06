<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    CourtCalendarService,
};

use App\Models\{
    CourtCalendar,
    Court,
    VenueSport,
    User,
};

class CourtCalendarController extends Controller
{
    public function index(Request $request)
    {
        $this->data['header']['title'] = __('template.court_calendars');
        $this->data['content'] = 'admin.court_calendar.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route('admin.dashboard'),
                'text' => __('template.dashboard'),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __('template.court_calendars'),
                'class' => 'active',
            ],
        ];
        $this->data['data']['status'] = [
            '10' => __('datatables.activated'),
            '20' => __('datatables.suspended'),
        ];
        $this->data['data']['courts'] = Court::where('status', 10)
            
            ->with(['venueSport.venue:id,name', 'venueSport.sport:id,name'])
            ->get();
        $this->data['data']['availability_status'] = [
            20 => __('Not Available'),
            10 => __('Available'),
        ];
        $this->data['data']['days_of_week'] = [
            'sunday'    => __('Sunday'),
            'monday'    => __('Monday'),
            'tuesday'   => __('Tuesday'),
            'wednesday' => __('Wednesday'),
            'thursday'  => __('Thursday'),
            'friday'    => __('Friday'),
            'saturday'  => __('Saturday'),
        ];
        $this->data['data']['section'] = 'events';

        return view('admin.main')->with($this->data);
    }

    public function userActivities(Request $request)
    {
        $this->data['header']['title'] = __('User Activities');
        $this->data['content'] = 'admin.court_calendar.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route('admin.dashboard'),
                'text' => __('template.dashboard'),
                'class' => '',
            ],
            [
                'url' => route('admin.module_parent.court_calendar.index'),
                'text' => __('template.court_calendars'),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __('User Activities'),
                'class' => 'active',
            ],
        ];
        $this->data['data']['status'] = [
            '10' => __('datatables.activated'),
            '20' => __('datatables.suspended'),
        ];
        $this->data['data']['courts'] = Court::where('status', 10)
            
            ->with(['venueSport.venue:id,name', 'venueSport.sport:id,name'])
            ->get();
        $this->data['data']['availability_status'] = [
            20 => __('Not Available'),
            10 => __('Available'),
        ];
        $this->data['data']['days_of_week'] = [
            'sunday'    => __('Sunday'),
            'monday'    => __('Monday'),
            'tuesday'   => __('Tuesday'),
            'wednesday' => __('Wednesday'),
            'thursday'  => __('Thursday'),
            'friday'    => __('Friday'),
            'saturday'  => __('Saturday'),
        ];
        $this->data['data']['section'] = 'user_activities';

        return view('admin.main')->with($this->data);
    }

    public function add(Request $request)
    {
        $this->data['header']['title'] = __('template.add_x', ['title' => 'Court Schedule']);
        $this->data['content'] = 'admin.court_calendar.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route('admin.dashboard'),
                'text' => __('template.dashboard'),
                'class' => '',
            ],
            [
                'url' => route('admin.module_parent.court_calendar.index'),
                'text' => __('template.court_calendars'),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __('template.add_x', ['title' => 'Court Schedule']),
                'class' => 'active',
            ],
        ];
        $this->data['data']['courts'] = Court::where('status', 10)
            
            ->with(['venueSport.venue:id,name', 'venueSport.sport:id,name'])
            ->get();

        $this->data['data']['users'] = User::where('status', 10)
            ->orderBy('fullname')
            ->get(['id', 'fullname', 'email']);

        return view('admin.main')->with($this->data);
    }

    public function edit(Request $request)
    {
        $this->data['header']['title'] = __('template.edit_x', ['title' => 'Court Schedule']);
        $this->data['content'] = 'admin.court_calendar.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route('admin.dashboard'),
                'text' => __('template.dashboard'),
                'class' => '',
            ],
            [
                'url' => route('admin.module_parent.court_calendar.index'),
                'text' => __('template.court_calendars'),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __('template.edit_x', ['title' => 'Court Schedule']),
                'class' => 'active',
            ],
        ];
        $this->data['data']['courts'] = Court::where('status', 10)
            
            ->with(['venueSport.venue:id,name', 'venueSport.sport:id,name'])
            ->get();

        $this->data['data']['users'] = User::where('status', 10)
            ->orderBy('fullname')
            ->get(['id', 'fullname', 'email']);

        return view('admin.main')->with($this->data);
    }

    public function allCourtCalendars(Request $request)
    {
        return CourtCalendarService::allCourtCalendars($request);
    }

    public function oneCourtCalendar(Request $request)
    {
        return CourtCalendarService::oneCourtCalendar($request);
    }

    public function createCourtCalendar(Request $request)
    {
        return CourtCalendarService::createCourtCalendar($request);
    }

    public function updateCourtCalendar(Request $request)
    {
        return CourtCalendarService::updateCourtCalendar($request);
    }

    public function updateCalendarStatus(Request $request)
    {
        return CourtCalendarService::updateCalendarStatus($request);
    }

    public function deleteCourtCalendar(Request $request)
    {
        return CourtCalendarService::deleteCourtCalendar($request);
    }

    public function getCourtAvailability(Request $request)
    {
        return CourtCalendarService::getCourtAvailability($request);
    }

    public function bulkCreateSchedules(Request $request)
    {
        return CourtCalendarService::bulkCreateSchedules($request);
    }

    public function getEventParticipants(Request $request)
    {
        return CourtCalendarService::getEventParticipants($request);
    }

    public function eventParticipants(Request $request)
    {
        $this->data['header']['title'] = __('template.event_joining_histories');
        $this->data['content'] = 'admin.court_calendar.event_participants';
        $this->data['breadcrumb'] = [
            [
                'url'   => route('admin.dashboard'),
                'text'  => __('template.dashboard'),
                'class' => '',
            ],
            [
                'url'   => route('admin.module_parent.court_calendar.index'),
                'text'  => __('template.court_calendars'),
                'class' => '',
            ],
            [
                'url'   => '',
                'text'  => __('template.event_joining_histories'),
                'class' => 'active',
            ],
        ];
        $this->data['data']['status'] = [
            '10' => __('court_calendar.confirmed'),
            '20' => __('court_calendar.cancelled'),
        ];

        return view('admin.main')->with($this->data);
    }

    public function allEventParticipants(Request $request)
    {
        return CourtCalendarService::allEventParticipants($request);
    }
}
