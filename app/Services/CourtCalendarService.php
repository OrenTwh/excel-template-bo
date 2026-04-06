<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Validator,
    Storage,
};

use Helper;

use App\Models\{
    CourtCalendar,
    CourtCalendarParticipant,
    Court,
};

use Carbon\Carbon;

class CourtCalendarService
{
    public static function createCourtCalendar($request)
    {
        $isEvent = (bool) $request->input('is_event', false);

        $validator = Validator::make($request->all(), [
            'court_id'              => ['required', 'exists:courts,id'],
            'date'                  => ['required', 'date'],
            'start_time'            => ['required', 'date_format:H:i'],
            'end_time'              => ['required', 'date_format:H:i', 'after:start_time'],
            'is_available'          => ['boolean'],
            'is_recurring'          => ['boolean'],
            'unavailability_reason' => ['nullable', 'string'],
            'special_price'         => ['nullable', 'numeric', 'min:0'],
            'is_event'              => ['boolean'],
            'event_title'           => ['required_if:is_event,1', 'nullable', 'string', 'max:255'],
            'event_description'     => ['nullable', 'string'],
            'max_participants'      => ['nullable', 'integer', 'min:1'],
            'price_per_participant' => ['nullable', 'numeric', 'min:0'],
            'external_form_link'    => ['nullable', 'url', 'max:2048'],
        ]);

        $attributeName = [
            'court_id'              => __('Court'),
            'date'                  => __('Date'),
            'start_time'            => __('Start Time'),
            'end_time'              => __('End Time'),
            'is_available'          => __('Is Available'),
            'is_recurring'          => __('Is Recurring'),
            'unavailability_reason' => __('Unavailability Reason'),
            'special_price'         => __('Special Price'),
            'is_event'              => __('Is Event'),
            'event_title'           => __('Event Title'),
            'event_description'     => __('Event Description'),
            'max_participants'      => __('Max Participants'),
            'price_per_participant' => __('Price per Participant'),
            'external_form_link'    => __('External Form Link'),
        ];

        foreach ($attributeName as $key => $aName) {
            $attributeName[$key] = strtolower($aName);
        }

        $validator->setAttributeNames($attributeName)->validate();

        DB::beginTransaction();

        try {
            $dayOfWeek = Carbon::parse($request->date)->format('l');

            $calendarData = [
                'court_id'              => $request->court_id,
                'date'                  => $request->date,
                'day_of_week'           => strtolower($dayOfWeek),
                'start_time'            => $request->start_time,
                'end_time'              => $request->end_time,
                'is_available'          => $request->has('is_available') ? $request->is_available : true,
                'is_recurring'          => $request->has('is_recurring') ? $request->is_recurring : false,
                'unavailability_reason' => $request->unavailability_reason,
                'special_price'         => $request->special_price,
                'is_event'              => $isEvent,
                'event_title'           => $isEvent ? $request->event_title           : null,
                'event_description'     => $isEvent ? $request->event_description     : null,
                'max_participants'      => $isEvent ? $request->max_participants       : null,
                'price_per_participant' => $isEvent ? $request->price_per_participant  : null,
                'external_form_link'    => $request->filled('external_form_link') ? $request->external_form_link : null,
                'status'                => 10,
                'created_by_user_id'    => $request->filled('created_by_user_id') ? $request->created_by_user_id : null,
            ];

            $calendar = CourtCalendar::create($calendarData);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500);
        }

        return response()->json([
            'message' => __('template.new_x_created', ['title' => 'Court Schedule']),
            'data' => [
                'id' => $calendar->id,
                'encrypted_id' => $calendar->encrypted_id,
            ],
            'status' => 200
        ]);
    }

    public static function updateCourtCalendar($request)
    {
        $request->merge([
            'id' => Helper::decode($request->id),
        ]);

        $isEvent = (bool) $request->input('is_event', false);

        $validator = Validator::make($request->all(), [
            'id'                    => ['required', 'exists:court_calendars,id'],
            'court_id'              => ['required', 'exists:courts,id'],
            'date'                  => ['required', 'date'],
            'start_time'            => ['required', 'date_format:H:i'],
            'end_time'              => ['required', 'date_format:H:i', 'after:start_time'],
            'is_available'          => ['boolean'],
            'is_recurring'          => ['boolean'],
            'unavailability_reason' => ['nullable', 'string'],
            'special_price'         => ['nullable', 'numeric', 'min:0'],
            'is_event'              => ['boolean'],
            'event_title'           => ['required_if:is_event,1', 'nullable', 'string', 'max:255'],
            'event_description'     => ['nullable', 'string'],
            'max_participants'      => ['nullable', 'integer', 'min:1'],
            'price_per_participant' => ['nullable', 'numeric', 'min:0'],
            'external_form_link'    => ['nullable', 'url', 'max:2048'],
        ]);

        $attributeName = [
            'court_id'              => __('Court'),
            'date'                  => __('Date'),
            'start_time'            => __('Start Time'),
            'end_time'              => __('End Time'),
            'is_available'          => __('Is Available'),
            'is_recurring'          => __('Is Recurring'),
            'unavailability_reason' => __('Unavailability Reason'),
            'special_price'         => __('Special Price'),
            'is_event'              => __('Is Event'),
            'event_title'           => __('Event Title'),
            'event_description'     => __('Event Description'),
            'max_participants'      => __('Max Participants'),
            'price_per_participant' => __('Price per Participant'),
            'external_form_link'    => __('External Form Link'),
        ];

        foreach ($attributeName as $key => $aName) {
            $attributeName[$key] = strtolower($aName);
        }

        $validator->setAttributeNames($attributeName)->validate();

        DB::beginTransaction();

        try {
            $calendar  = CourtCalendar::find($request->id);
            $dayOfWeek = Carbon::parse($request->date)->format('l');

            $calendar->court_id              = $request->court_id;
            $calendar->date                  = $request->date;
            $calendar->day_of_week           = strtolower($dayOfWeek);
            $calendar->start_time            = $request->start_time;
            $calendar->end_time              = $request->end_time;
            $calendar->is_available          = $request->has('is_available') ? $request->is_available : true;
            $calendar->is_recurring          = $request->has('is_recurring') ? $request->is_recurring : false;
            $calendar->unavailability_reason = $request->unavailability_reason;
            $calendar->special_price         = $request->special_price;
            $calendar->is_event              = $isEvent;
            $calendar->event_title           = $isEvent ? $request->event_title           : null;
            $calendar->event_description     = $isEvent ? $request->event_description     : null;
            $calendar->max_participants      = $isEvent ? $request->max_participants       : null;
            $calendar->price_per_participant = $isEvent ? $request->price_per_participant  : null;
            $calendar->external_form_link    = $request->filled('external_form_link') ? $request->external_form_link : null;
            $calendar->created_by_user_id    = $request->filled('created_by_user_id') ? $request->created_by_user_id : null;

            $calendar->save();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500);
        }

        return response()->json([
            'message' => __('template.x_updated', ['title' => 'Court Schedule']),
        ]);
    }

    public static function allCourtCalendars($request)
    {
        $calendars = CourtCalendar::select('court_calendars.*')
            ->with(['court:id,name', 'createdBy:id,fullname,email'])
            ->withCount(['participants as participants_count' => function ($q) {
                $q->where('status', CourtCalendarParticipant::STATUS_CONFIRMED);
            }]);

        $filterObject = self::filter($request, $calendars);
        $calendar = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ($request->input('order.0.column') != 0) {
            $dir = $request->input('order.0.dir');
            switch ($request->input('order.0.column')) {
                case 1:
                    $calendar->orderBy('court_calendars.date', $dir);
                    break;
                case 2:
                    $calendar->orderBy('court_calendars.start_time', $dir);
                    break;
                case 3:
                    $calendar->orderBy('court_calendars.is_available', $dir);
                    break;
                case 4:
                    $calendar->orderBy('court_calendars.created_at', $dir);
                    break;
            }
        } else {
            $calendar->orderBy('court_calendars.date', 'asc')
                ->orderBy('court_calendars.start_time', 'asc');
        }

        $calendarCount = $calendar->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $calendars = $calendar->skip($offset)->take($limit)->get();

        if ($calendars) {
            $calendars->append([
                'encrypted_id',
            ]);
        }

        $totalRecord = CourtCalendar::count();

        $data = [
            'calendars' => $calendars,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $calendarCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return response()->json($data);
    }

    private static function filter($request, $model)
    {
        $filter = false;

        if (!empty($request->type)) {
            if ($request->type === 'activities') {
                $model->whereNotNull('court_calendars.created_by_user_id');
            } elseif ($request->type === 'events') {
                $model->whereNull('court_calendars.created_by_user_id');
            }
            $filter = true;
        }

        if (!empty($request->court_id)) {
            $model->where('court_calendars.court_id', $request->court_id);
            $filter = true;
        }

        if (!empty($request->date)) {
            $model->whereDate('court_calendars.date', $request->date);
            $filter = true;
        }

        if (!empty($request->day_of_week)) {
            $model->where('court_calendars.day_of_week', $request->day_of_week);
            $filter = true;
        }

        if (!empty($request->status)) {
            $model->where('court_calendars.status', $request->status);
            $filter = true;
        }

        if (isset($request->is_recurring)) {
            $model->where('is_recurring', $request->is_recurring);
            $filter = true;
        }

        if (!empty($request->status)) {
            $model->where('status', $request->status);
            $filter = true;
        }

        if (!empty($request->date_from) && !empty($request->date_to)) {
            $model->whereBetween('court_calendars.date', [$request->date_from, $request->date_to]);
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneCourtCalendar($request)
    {
        $request->merge([
            'id' => Helper::decode($request->id),
        ]);

        $calendar = CourtCalendar::with('court')
            ->withCount(['participants as participants_count' => function ($q) {
                $q->where('status', CourtCalendarParticipant::STATUS_CONFIRMED);
            }])
            ->find($request->id);

        $calendar->append(['encrypted_id']);

        return response()->json($calendar);
    }

    public static function allEventParticipants($request)
    {
        $query = CourtCalendarParticipant::select('court_calendar_participants.*')
            ->with([
                'courtCalendar:id,event_title,date,start_time,end_time',
                'user:id,fullname,email,calling_code,phone_number',
            ])
            ->whereHas('courtCalendar', fn($q) => $q->where('is_event', true));

        $filter = false;

        if (!empty($request->event_title)) {
            $query->whereHas('courtCalendar', fn($q) =>
                $q->where('event_title', 'LIKE', '%' . $request->event_title . '%'));
            $filter = true;
        }

        if (!empty($request->status)) {
            $query->where('court_calendar_participants.status', $request->status);
            $filter = true;
        }

        if (!empty($request->event_date)) {
            if (str_contains($request->event_date, 'to')) {
                $dates = explode(' to ', $request->event_date);
                $query->whereHas('courtCalendar', fn($q) =>
                    $q->whereDate('date', '>=', $dates[0])
                      ->whereDate('date', '<=', $dates[1]));
            } else {
                $query->whereHas('courtCalendar', fn($q) =>
                    $q->whereDate('date', $request->event_date));
            }
            $filter = true;
        }

        if (!empty($request->user)) {
            $query->whereHas('user', fn($q) =>
                $q->where('name', 'LIKE', '%' . $request->user . '%')
                  ->orWhere('email', 'LIKE', '%' . $request->user . '%'));
            $filter = true;
        }

        $total = (clone $query)->count();
        $totalAll = CourtCalendarParticipant::whereHas('courtCalendar', fn($q) => $q->where('is_event', true))->count();

        $limit  = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $rows = $query->orderBy('court_calendar_participants.joined_at', 'desc')
            ->skip($offset)->take($limit)->get();

        foreach ($rows as $row) {
            $row->append('encrypted_id');
        }

        return response()->json([
            'participants'    => $rows,
            'draw'            => $request->draw,
            'recordsFiltered' => $filter ? $total : $totalAll,
            'recordsTotal'    => $totalAll,
        ]);
    }

    public static function getEventParticipants($request)
    {
        $request->merge([
            'id' => Helper::decode($request->id),
        ]);

        $calendar = CourtCalendar::find($request->id);

        if (!$calendar || !$calendar->is_event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        $participants = CourtCalendarParticipant::where('court_calendar_id', $calendar->id)
            ->with('user:id,fullname,email,calling_code,phone_number')
            ->orderBy('joined_at', 'asc')
            ->get();

        return response()->json([
            'event'        => [
                'id'           => $calendar->encrypted_id,
                'event_title'  => $calendar->event_title,
                'date'         => $calendar->date?->format('Y-m-d'),
                'start_time'   => $calendar->start_time,
                'end_time'     => $calendar->end_time,
                'max_participants' => $calendar->max_participants,
            ],
            'participants' => $participants->map(fn($p) => [
                'id'                  => $p->encrypted_id,
                'name'                => $p->user?->name,
                'email'               => $p->user?->email,
                'phone'               => trim(($p->user?->calling_code ?? '') . ' ' . ($p->user?->phone_number ?? '')),
                'status'              => $p->status_label,
                'joined_at'           => $p->joined_at,
                'cancelled_at'        => $p->cancelled_at,
                'cancellation_reason' => $p->cancellation_reason,
            ]),
            'total' => $participants->count(),
        ]);
    }

    public static function deleteCourtCalendar($request)
    {
        $request->merge([
            'id' => Helper::decode($request->id),
        ]);

        $validator = Validator::make($request->all(), [
            'id' => ['required'],
        ]);

        $attributeName = [
            'id' => __('ID'),
        ];

        foreach ($attributeName as $key => $aName) {
            $attributeName[$key] = strtolower($aName);
        }

        $validator->setAttributeNames($attributeName)->validate();

        DB::beginTransaction();

        try {
            $calendar = CourtCalendar::find($request->id);
            $calendar->delete();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500);
        }

        return response()->json([
            'message' => __('template.x_deleted', ['title' => 'Court Schedule']),
        ]);
    }

    public static function updateCalendarStatus($request)
    {
        $request->merge([
            'id' => Helper::decode($request->id),
        ]);

        DB::beginTransaction();

        try {
            $calendar = CourtCalendar::find($request->id);
            $calendar->status = $calendar->status == 10 ? 20 : 10;

            $calendar->save();
            DB::commit();

            return response()->json([
                'data' => [
                    'calendar' => $calendar,
                    'message_key' => 'update_calendar_success',
                ]
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'update_calendar_failed',
            ], 500);
        }
    }

    public static function getCourtAvailability($request)
    {
        $validator = Validator::make($request->all(), [
            'court_id' => ['required', 'exists:courts,id'],
            'date' => ['required', 'date'],
        ]);

        $validator->validate();

        $court = Court::find($request->court_id);
        $date = Carbon::parse($request->date);
        $dayOfWeek = strtolower($date->format('l'));

        // Get calendar entries for this court and date
        $calendarEntries = CourtCalendar::where('court_id', $request->court_id)
            ->where(function ($query) use ($request, $dayOfWeek) {
                $query->where('date', $request->date)
                    ->orWhere(function ($q) use ($dayOfWeek) {
                        $q->where('is_recurring', true)
                            ->where('day_of_week', $dayOfWeek);
                    });
            })
            ->where('status', 10)
            ->get();

        return response()->json([
            'court' => $court,
            'date' => $request->date,
            'day_of_week' => $dayOfWeek,
            'calendar_entries' => $calendarEntries,
        ]);
    }

    public static function bulkCreateSchedules($request)
    {
        $validator = Validator::make($request->all(), [
            'court_id' => ['required', 'exists:courts,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'days_of_week' => ['required', 'array'],
            'days_of_week.*' => ['in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'is_available' => ['boolean'],
            'special_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $validator->validate();

        DB::beginTransaction();

        try {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $daysOfWeek = $request->days_of_week;

            $created = 0;

            while ($startDate->lte($endDate)) {
                $currentDayOfWeek = strtolower($startDate->format('l'));

                if (in_array($currentDayOfWeek, $daysOfWeek)) {
                    // Check if entry already exists
                    $exists = CourtCalendar::where('court_id', $request->court_id)
                        ->where('date', $startDate->format('Y-m-d'))
                        ->where('start_time', $request->start_time)
                        ->where('end_time', $request->end_time)
                        ->exists();

                    if (!$exists) {
                        CourtCalendar::create([
                            'court_id' => $request->court_id,
                            'date' => $startDate->format('Y-m-d'),
                            'day_of_week' => $currentDayOfWeek,
                            'start_time' => $request->start_time,
                            'end_time' => $request->end_time,
                            'is_available' => $request->has('is_available') ? $request->is_available : true,
                            'is_recurring' => false,
                            'special_price' => $request->special_price,
                            'status' => 10,
                        ]);

                        $created++;
                    }
                }

                $startDate->addDay();
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500);
        }

        return response()->json([
            'message' => __('Created :count schedule entries', ['count' => $created]),
            'data' => [
                'created_count' => $created,
            ],
        ]);
    }
}
