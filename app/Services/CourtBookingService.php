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
    CourtBooking,
    CourtBookingGroup,
    Court,
    User,
    Voucher,
};

use App\Helpers\CourtAvailabilityHelper;

use Carbon\Carbon;

class CourtBookingService
{
    public static function createCourtBooking($request)
    {
        $validator = Validator::make($request->all(), [
            'court_id'     => ['required', 'exists:courts,id'],
            'user_id'      => ['required', 'exists:users,id'],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time'   => ['required', 'date_format:H:i'],
            'end_time'     => ['required', 'date_format:H:i', 'after:start_time'],
            'participants' => ['nullable', 'integer', 'min:1'],
            'notes'        => ['nullable', 'string'],
            'voucher_id'   => ['nullable', 'exists:vouchers,id'],
        ]);

        $attributeName = [
            'court_id'     => __('court_booking.court'),
            'user_id'      => __('court_booking.user'),
            'booking_date' => __('court_booking.booking Date'),
            'start_time'   => __('court_booking.start Time'),
            'end_time'     => __('court_booking.end Time'),
            'participants' => __('court_booking.participants'),
            'notes'        => __('court_booking.notes'),
            'voucher_id'   => __('court_booking.voucher'),
        ];

        foreach ($attributeName as $key => $aName) {
            $attributeName[$key] = strtolower($aName);
        }

        $validator->setAttributeNames($attributeName)->validate();

        $participants = (int) ($request->participants ?? 1);

        // Check availability
        $isAvailable = self::isCourtAvailable(
            $request->court_id,
            $request->booking_date,
            $request->start_time,
            $request->end_time,
            null,
            $participants
        );

        if (!$isAvailable) {
            return response()->json([
                'message' => __('Court is not available for the selected time slot'),
                'errors'  => ['booking' => __('Court is not available for the selected time slot')],
            ], 422);
        }

        $court      = Court::with('venueSport')->find($request->court_id);
        $venueSport = $court->venueSport;

        $startTime = Carbon::parse($request->start_time);
        $endTime   = Carbon::parse($request->end_time);

        if ($venueSport) {
            $openTime  = Carbon::parse($venueSport->open_time);
            $closeTime = Carbon::parse($venueSport->close_time);

            if ($startTime->lt($openTime) || $endTime->gt($closeTime)) {
                return response()->json([
                    'message' => __('Booking time must be within operating hours (:open – :close)', [
                        'open'  => $venueSport->open_time,
                        'close' => $venueSport->close_time,
                    ]),
                    'errors'  => ['start_time' => __('Booking time must be within operating hours')],
                ], 422);
            }
        }

        DB::beginTransaction();

        try {
            $slotDuration  = $venueSport ? (int) $venueSport->slot_duration  : 60;
            $pricePerSlot  = $venueSport ? (float) $venueSport->price_per_slot : (float) $court->price_per_hour;
            $durationMins  = $endTime->diffInMinutes($startTime);
            $slots         = $slotDuration > 0 ? (int) floor($durationMins / $slotDuration) : 1;
            $durationHours = $endTime->diffInHours($startTime);
            $subtotal      = round($slots * $pricePerSlot, 2);

            $discountAmount = 0;
            $voucherId      = $request->voucher_id ?? null;

            // ── Create the booking group ──────────────────────────────────────
            $group = CourtBookingGroup::create([
                'user_id'         => $request->user_id,
                'group_no'        => self::generateBookingNumber(),
                'voucher_id'      => $voucherId,
                'subtotal'        => $subtotal,
                'discount_amount' => $discountAmount,
                'total_amount'    => $subtotal - $discountAmount,
                'payment_status'  => 'pending',
                'payment_gateway' => 'manual',
                'status'          => CourtBookingGroup::STATUS_PENDING_PAYMENT,
            ]);

            // ── Create the single court item ──────────────────────────────────
            CourtBooking::create([
                'court_booking_group_id' => $group->id,
                'court_id'               => $request->court_id,
                'booking_date'           => $request->booking_date,
                'start_time'             => $request->start_time,
                'end_time'               => $request->end_time,
                'duration_hours'         => $durationHours,
                'participants'           => $participants,
                'price'                  => $pricePerSlot,
                'total_amount'           => $subtotal - $discountAmount,
                'discount_amount'        => $discountAmount,
                'notes'                  => $request->notes,
            ]);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500);
        }

        return response()->json([
            'message' => __('template.new_x_created', ['title' => 'Court Booking']),
            'data'    => [
                'id'           => $group->id,
                'encrypted_id' => $group->encrypted_id,
                'booking_no'   => $group->group_no,
            ],
            'status' => 200,
        ]);
    }

    public static function updateCourtBooking($request)
    {
        $request->merge([
            'id' => Helper::decode($request->id),
        ]);

        $validator = Validator::make($request->all(), [
            'id'             => ['required', 'exists:court_booking_groups,id'],
            'court_id'       => ['required', 'exists:courts,id'],
            'user_id'        => ['required', 'exists:users,id'],
            'booking_date'   => ['required', 'date'],
            'start_time'     => ['required', 'date_format:H:i'],
            'end_time'       => ['required', 'date_format:H:i', 'after:start_time'],
            'status'         => ['nullable', 'integer'],
            'payment_status' => ['nullable', 'string', 'in:pending,paid,failed,refunded'],
            'notes'          => ['nullable', 'string'],
        ]);

        $attributeName = [
            'court_id'     => __('court_booking.court'),
            'user_id'      => __('court_booking.user'),
            'booking_date' => __('court_booking.booking_date'),
            'start_time'   => __('court_booking.start_time'),
            'end_time'     => __('court_booking.end_time'),
            'notes'        => __('court_booking.notes'),
        ];

        foreach ($attributeName as $key => $aName) {
            $attributeName[$key] = strtolower($aName);
        }

        $validator->setAttributeNames($attributeName)->validate();

        $court      = Court::with('venueSport')->find($request->court_id);
        $venueSport = $court->venueSport;

        $startTime = Carbon::parse($request->start_time);
        $endTime   = Carbon::parse($request->end_time);

        if ($venueSport) {
            $openTime  = Carbon::parse($venueSport->open_time);
            $closeTime = Carbon::parse($venueSport->close_time);

            if ($startTime->lt($openTime) || $endTime->gt($closeTime)) {
                return response()->json([
                    'message' => __('Booking time must be within operating hours (:open – :close)', [
                        'open'  => $venueSport->open_time,
                        'close' => $venueSport->close_time,
                    ]),
                    'errors'  => ['start_time' => __('Booking time must be within operating hours')],
                ], 422);
            }
        }

        DB::beginTransaction();

        try {
            $group = CourtBookingGroup::find($request->id);

            $slotDuration  = $venueSport ? (int) $venueSport->slot_duration  : 60;
            $pricePerSlot  = $venueSport ? (float) $venueSport->price_per_slot : (float) $court->price_per_hour;
            $durationMins  = $endTime->diffInMinutes($startTime);
            $slots         = $slotDuration > 0 ? (int) floor($durationMins / $slotDuration) : 1;
            $durationHours = $endTime->diffInHours($startTime);
            $subtotal      = round($slots * $pricePerSlot, 2);

            // Update group-level fields
            $group->user_id   = $request->user_id;
            $group->subtotal  = $subtotal;
            $group->total_amount = $subtotal - $group->discount_amount;

            if ($request->filled('status')) {
                $group->status = (int) $request->status;
            }
            if ($request->filled('payment_status')) {
                $group->payment_status = $request->payment_status;
            }

            $group->save();

            // Update first court item (admin creates single-court groups)
            $item = $group->courtBookings()->first();
            if ($item) {
                $item->court_id       = $request->court_id;
                $item->booking_date   = $request->booking_date;
                $item->start_time     = $request->start_time;
                $item->end_time       = $request->end_time;
                $item->duration_hours = $durationHours;
                $item->price          = $pricePerSlot;
                $item->total_amount   = $subtotal - $item->discount_amount;
                $item->notes          = $request->notes;
                $item->save();
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500);
        }

        return response()->json([
            'message' => __('template.x_updated', ['title' => 'Court Booking']),
        ]);
    }

    public static function allCourtBookings($request)
    {
        $groups = CourtBookingGroup::select('court_booking_groups.*')
            ->with([
                'courtBookings:id,court_booking_group_id,court_id,booking_date,start_time,end_time',
                'courtBookings.court:id,name',
                'user:id,fullname,email',
            ]);

        $filterObject = self::filter($request, $groups);
        $group        = $filterObject['model'];
        $filter       = $filterObject['filter'];

        if ($request->input('order.0.column') != 0) {
            $dir = $request->input('order.0.dir');
            switch ($request->input('order.0.column')) {
                case 1: $group->orderBy('court_booking_groups.group_no', $dir);   break;
                case 2: $group->orderBy('court_booking_groups.created_at', $dir); break;
                case 3: $group->orderBy('court_booking_groups.status', $dir);     break;
                case 4: $group->orderBy('court_booking_groups.created_at', $dir); break;
            }
        } else {
            $group->orderBy('court_booking_groups.created_at', 'desc');
        }

        $groupCount = $group->count();

        $limit  = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $results = $group->skip($offset)->take($limit)->get();

        if ($results) {
            $results->append(['encrypted_id', 'status_label']);
        }

        $totalRecord = CourtBookingGroup::count();

        return response()->json([
            'court_bookings'  => $results,
            'draw'            => $request->draw,
            'recordsFiltered' => $filter ? $groupCount : $totalRecord,
            'recordsTotal'    => $totalRecord,
        ]);
    }

    private static function filter($request, $model)
    {
        $filter = false;

        if (!empty($request->booking_no)) {
            $model->where('court_booking_groups.group_no', 'LIKE', '%' . $request->booking_no . '%');
            $filter = true;
        }

        if (!empty($request->court_id)) {
            $model->whereHas('courtBookings', function ($q) use ($request) {
                $q->where('court_id', $request->court_id);
            });
            $filter = true;
        }

        if (!empty($request->user_id)) {
            $decodedUserId = Helper::decode($request->user_id);
            if ($decodedUserId) {
                $model->where('court_booking_groups.user_id', $decodedUserId);
                $filter = true;
            }
        }

        if (!empty($request->user_name)) {
            $model->whereHas('user', function ($query) use ($request) {
                $query->where('email', 'LIKE', '%' . $request->user_name . '%');
            });
            $filter = true;
        }

        if (!empty($request->payment_status)) {
            $model->where('court_booking_groups.payment_status', $request->payment_status);
            $filter = true;
        }

        if (!empty($request->booking_date)) {
            $model->whereHas('courtBookings', function ($q) use ($request) {
                $q->whereDate('booking_date', $request->booking_date);
            });
            $filter = true;
        }

        if (!empty($request->status)) {
            $model->where('court_booking_groups.status', $request->status);
            $filter = true;
        }

        if (!empty($request->status_filter)) {
            $values = is_array($request->status_filter)
                ? $request->status_filter
                : array_filter(explode(',', $request->status_filter));
            $model->whereIn('court_booking_groups.status', $values);
            $filter = true;
        }

        if (!empty($request->custom_search)) {
            $model->where('court_booking_groups.group_no', 'LIKE', '%' . $request->custom_search . '%');
            $filter = true;
        }

        return ['filter' => $filter, 'model' => $model];
    }

    public static function oneCourtBooking($request)
    {
        $request->merge([
            'id' => Helper::decode($request->id),
        ]);

        $group = CourtBookingGroup::with(['courtBookings.court', 'user', 'voucher'])->find($request->id);
        $group->append(['encrypted_id', 'status_label']);

        return response()->json($group);
    }

    public static function deleteCourtBooking($request)
    {
        $request->merge([
            'id' => Helper::decode($request->id),
        ]);

        Validator::make($request->all(), [
            'id' => ['required'],
        ])->validate();

        DB::beginTransaction();

        try {
            CourtBookingGroup::findOrFail($request->id)->delete();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500);
        }

        return response()->json([
            'message' => __('template.x_deleted', ['title' => 'Court Booking']),
        ]);
    }

    public static function updateBookingStatus($request)
    {
        $request->merge([
            'id' => Helper::decode($request->id),
        ]);

        $allowedStatuses = array_keys(CourtBookingGroup::statusLabels());

        Validator::make($request->all(), [
            'id'     => ['required', 'exists:court_booking_groups,id'],
            'status' => ['required', 'integer', 'in:' . implode(',', $allowedStatuses)],
        ])->validate();

        DB::beginTransaction();

        try {
            $group         = CourtBookingGroup::find($request->id);
            $group->status = (int) $request->status;
            $group->save();

            DB::commit();

            return response()->json([
                'data' => [
                    'booking'     => $group->append(['encrypted_id', 'status_label']),
                    'message_key' => 'update_booking_success',
                ],
            ]);
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json([
                'message'     => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'update_booking_failed',
            ], 500);
        }
    }

    public static function updatePaymentStatus($request)
    {
        $request->merge([
            'id' => Helper::decode($request->id),
        ]);

        Validator::make($request->all(), [
            'id'             => ['required', 'exists:court_booking_groups,id'],
            'payment_status' => ['required', 'in:pending,paid,failed,refunded'],
        ])->validate();

        DB::beginTransaction();

        try {
            $group                 = CourtBookingGroup::find($request->id);
            $group->payment_status = $request->payment_status;

            if ($request->payment_status === 'paid' && $group->status === CourtBookingGroup::STATUS_PENDING_PAYMENT) {
                $group->status       = CourtBookingGroup::STATUS_UPCOMING;
                $group->confirmed_at = Carbon::now();
            }

            $group->save();
            DB::commit();

            return response()->json([
                'message' => __('Payment status updated successfully'),
                'data'    => ['booking' => $group->append(['encrypted_id', 'status_label'])],
            ]);
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500);
        }
    }

    public static function confirmBooking($request)
    {
        $request->merge([
            'id' => Helper::decode($request->id),
        ]);

        DB::beginTransaction();

        try {
            $group               = CourtBookingGroup::find($request->id);
            $group->status       = CourtBookingGroup::STATUS_UPCOMING;
            $group->confirmed_at = Carbon::now();
            $group->save();

            DB::commit();

            return response()->json([
                'message' => __('Booking confirmed successfully'),
                'data'    => ['booking' => $group->append(['encrypted_id', 'status_label'])],
            ]);
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500);
        }
    }

    public static function cancelBooking($request)
    {
        $request->merge([
            'id' => Helper::decode($request->id),
        ]);

        Validator::make($request->all(), [
            'id'                  => ['required', 'exists:court_booking_groups,id'],
            'cancellation_reason' => ['required', 'string'],
        ])->validate();

        DB::beginTransaction();

        try {
            $group                        = CourtBookingGroup::find($request->id);
            $group->status                = CourtBookingGroup::STATUS_CANCELED;
            $group->cancelled_at          = Carbon::now();
            $group->cancellation_reason   = $request->cancellation_reason;
            $group->save();

            DB::commit();

            return response()->json([
                'message' => __('Booking cancelled successfully'),
                'data'    => ['booking' => $group->append(['encrypted_id', 'status_label'])],
            ]);
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500);
        }
    }

    public static function checkAvailability($request)
    {
        Validator::make($request->all(), [
            'court_id'     => ['required', 'exists:courts,id'],
            'booking_date' => ['required', 'date'],
            'start_time'   => ['required', 'date_format:H:i'],
            'end_time'     => ['required', 'date_format:H:i', 'after:start_time'],
            'participants' => ['nullable', 'integer', 'min:1'],
        ])->validate();

        $participants = (int) ($request->participants ?? 1);

        $isAvailable = self::isCourtAvailable(
            $request->court_id,
            $request->booking_date,
            $request->start_time,
            $request->end_time,
            $request->booking_id ?? null,
            $participants
        );

        $reason = $isAvailable ? null : CourtAvailabilityHelper::getUnavailabilityReason(
            $request->court_id,
            $request->booking_date,
            $request->start_time,
            $request->end_time
        );

        return response()->json([
            'available' => $isAvailable,
            'message'   => $isAvailable
                ? __('Court is available')
                : ($reason ?? __('Court is not available for the selected time slot')),
            'reason'    => $reason,
        ]);
    }

    public static function getBookingsByDate($request)
    {
        Validator::make($request->all(), [
            'date_from' => ['required', 'date_format:Y-m-d'],
            'date_to'   => ['required', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ])->validate();

        $query = CourtBooking::whereBetween('court_bookings.booking_date', [$request->date_from, $request->date_to])
            ->with([
                'group:id,user_id,status,payment_status',
                'group.user:id,fullname,phone_number,calling_code',
                'court:id,name,venue_sport_id,capacity',
                'court.venueSport.venue:id,name',
                'court.venueSport.sport:id,name',
            ])
            ->orderBy('court_bookings.booking_date')
            ->orderBy('court_bookings.start_time');

        if ($request->filled('court_id')) {
            $query->where('court_bookings.court_id', Helper::decode($request->court_id));
        }

        if ($request->filled('venue_id')) {
            $venueId = Helper::decode($request->venue_id);
            $query->whereHas('court.venueSport', fn($q) => $q->where('venue_id', $venueId));
        }

        if ($request->filled('status_filter')) {
            $values = is_array($request->status_filter)
                ? $request->status_filter
                : array_filter(explode(',', $request->status_filter));
            $query->whereHas('group', fn($q) => $q->whereIn('status', $values));
        }

        $bookings = $query->get();
        $bookings->each(function ($item) {
            $item->append(['encrypted_id']);
            $item->group?->append(['encrypted_id']);
        });

        return response()->json([
            'status'    => 'success',
            'date_from' => $request->date_from,
            'date_to'   => $request->date_to,
            'bookings'  => $bookings,
        ]);
    }

    public static function getAvailabilityData($request)
    {
        Validator::make($request->all(), [
            'date_from' => ['required', 'date_format:Y-m-d'],
            'date_to'   => ['required', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ])->validate();

        $dateFrom = Carbon::parse($request->date_from);
        $dateTo   = Carbon::parse($request->date_to);

        // Cap to 31 days to keep the grid manageable
        if ($dateFrom->diffInDays($dateTo) > 30) {
            $dateTo = $dateFrom->copy()->addDays(30);
        }

        // ── Courts ────────────────────────────────────────────────────────────
        $courtQuery = Court::where('status', 10)
            ->with([
                'venueSport:id,venue_id,sport_id,open_time,close_time,slot_duration',
                'venueSport.venue:id,name',
                'venueSport.sport:id,name',
            ]);

        if ($request->filled('venue_id')) {
            $venueId = Helper::decode($request->venue_id);
            $courtQuery->whereHas('venueSport', fn($q) => $q->where('venue_id', $venueId));
        }

        if ($request->filled('court_id')) {
            $courtQuery->where('id', Helper::decode($request->court_id));
        }

        $courts   = $courtQuery->orderBy('name')->get(['id', 'name', 'venue_sport_id', 'capacity']);
        $courtIds = $courts->pluck('id')->all();

        if (empty($courtIds)) {
            return response()->json([
                'status'    => 'success',
                'date_from' => $dateFrom->format('Y-m-d'),
                'date_to'   => $dateTo->format('Y-m-d'),
                'dates'     => [],
                'courts'    => [],
                'bookings'  => [],
                'blocks'    => [],
            ]);
        }

        // ── Active bookings in range ───────────────────────────────────────────
        $rawBookings = CourtBooking::whereIn('court_bookings.court_id', $courtIds)
            ->whereBetween('court_bookings.booking_date', [$dateFrom->format('Y-m-d'), $dateTo->format('Y-m-d')])
            ->whereHas('group', function ($q) {
                $q->whereIn('status', [
                    CourtBookingGroup::STATUS_PENDING_PAYMENT,
                    CourtBookingGroup::STATUS_UPCOMING,
                    CourtBookingGroup::STATUS_COMPLETE,
                ]);
            })
            ->with('group:id,status')
            ->select('court_bookings.id', 'court_bookings.court_booking_group_id', 'court_bookings.court_id', 'court_bookings.booking_date', 'court_bookings.start_time', 'court_bookings.end_time', 'court_bookings.participants')
            ->get();

        // Group bookings: [date][court_id] => [{start, end, participants, status, encrypted_id}]
        $bookings = [];
        foreach ($rawBookings as $b) {
            $date    = Carbon::parse($b->booking_date)->format('Y-m-d');
            $cId     = $b->court_id;
            $bookings[$date][$cId][] = [
                'start_time'        => Carbon::parse($b->start_time)->format('H:i'),
                'end_time'          => Carbon::parse($b->end_time)->format('H:i'),
                'participants'      => (int) ($b->participants ?? 1),
                'status'            => $b->group?->status,
                'encrypted_id'      => Helper::encode($b->id),
                'group_encrypted_id'=> $b->group ? Helper::encode($b->group->id) : null,
            ];
        }

        // ── Calendar blocks in range ───────────────────────────────────────────
        $rawBlocks = CourtAvailabilityHelper::getCalendarBlocksBulk(
            $courtIds,
            $dateFrom->format('Y-m-d'),
            $dateTo->format('Y-m-d')
        );

        $blocks = [];
        foreach ($rawBlocks as $date => $courtMap) {
            foreach ($courtMap as $cId => $blkList) {
                $blocks[$date][$cId] = $blkList->map(fn($b) => [
                    'start_time'   => $b->start_time,
                    'end_time'     => $b->end_time,
                    'encrypted_id' => Helper::encode($b->id),
                ])->values()->toArray();
            }
        }

        // ── Courts payload ────────────────────────────────────────────────────
        $courtsData = $courts->map(function ($c) {
            $vs = $c->venueSport;
            return [
                'id'            => $c->id,
                'name'          => $c->name,
                'capacity'      => (int) $c->capacity,
                'venue_name'    => $vs?->venue?->name ?? '—',
                'sport_name'    => $vs?->sport?->name ?? '—',
                'open_time'     => $vs?->open_time  ? Carbon::parse($vs->open_time)->format('H:i')  : '08:00',
                'close_time'    => $vs?->close_time ? Carbon::parse($vs->close_time)->format('H:i') : '22:00',
                'slot_duration' => (int) ($vs?->slot_duration ?? 60),
            ];
        })->values()->toArray();

        // ── Date list ─────────────────────────────────────────────────────────
        $dates   = [];
        $current = $dateFrom->copy();
        while ($current->lte($dateTo)) {
            $dates[] = $current->format('Y-m-d');
            $current->addDay();
        }

        return response()->json([
            'status'    => 'success',
            'date_from' => $dateFrom->format('Y-m-d'),
            'date_to'   => $dateTo->format('Y-m-d'),
            'dates'     => $dates,
            'courts'    => $courtsData,
            'bookings'  => $bookings,
            'blocks'    => $blocks,
        ]);
    }

    private static function isCourtAvailable($courtId, $bookingDate, $startTime, $endTime, $excludeBookingId = null, $participants = 1)
    {
        return CourtAvailabilityHelper::isCourtAvailable(
            $courtId,
            $bookingDate,
            $startTime,
            $endTime,
            $excludeBookingId,
            $participants
        );
    }

    public static function generateBookingNumber()
    {
        return 'CB-' . Carbon::now()->format('Ymd') . '-' . strtoupper(Str::random(6));
    }

    // ── API methods ───────────────────────────────────────────────────────────

    public static function getMyBookings($request)
    {
        $query = CourtBookingGroup::where('user_id', auth()->user()->id)
            ->with([
                'courtBookings:id,court_booking_group_id,court_id,booking_date,start_time,end_time,participants,price,total_amount,discount_amount,notes',
                'courtBookings.court:id,venue_sport_id,name,image',
                'courtBookings.court.venueSport.venue:id,name,address_1,city,state,image',
                'courtBookings.court.venueSport.sport:id,name,slug,icon',
                'voucher:id,promo_code,discount_type,discount_amount',
            ]);

        if ($request->filled('status')) {
            $query->where('status', (int) $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereHas('courtBookings', fn($q) => $q->whereDate('booking_date', '>=', $request->date_from));
        }

        if ($request->filled('date_to')) {
            $query->whereHas('courtBookings', fn($q) => $q->whereDate('booking_date', '<=', $request->date_to));
        }

        $groups = $query->orderBy('created_at', 'desc')
                        ->paginate($request->input('per_page', 15));

        $groups->getCollection()->each(function ($group) {
            $group->append(['encrypted_id', 'status_label']);
            $group->receipt_url = self::receiptUrl($group->encrypted_id);
            $group->courtBookings->each(function ($item) {
                $item->append(['encrypted_id']);
                $item->court?->append(['encrypted_id', 'image_path']);
                $item->court?->venueSport?->venue?->append(['encrypted_id', 'image_path']);
                $item->court?->venueSport?->sport?->append(['encrypted_id', 'icon_path']);
            });
        });

        return $groups;
    }

    public static function getOneBooking($request)
    {
        $id    = Helper::decode($request->id);
        $group = CourtBookingGroup::where('user_id', auth()->user()->id)
            ->with([
                'courtBookings:id,court_booking_group_id,court_id,booking_date,start_time,end_time,participants,price,total_amount,discount_amount,notes',
                'courtBookings.court:id,venue_sport_id,name,capacity,image',
                'courtBookings.court.venueSport:id,venue_id,sport_id,slot_duration,price_per_slot,open_time,close_time',
                'courtBookings.court.venueSport.venue:id,name,address_1,address_2,city,state,postcode,gmap_link,waze_link,image',
                'courtBookings.court.venueSport.sport:id,name,slug,icon',
                'voucher:id,promo_code,discount_type,discount_amount',
            ])
            ->find($id);

        if (!$group) {
            return null;
        }

        $group->append(['encrypted_id', 'status_label']);
        $group->receipt_url = self::receiptUrl($group->encrypted_id);
        $group->courtBookings->each(function ($item) {
            $item->append(['encrypted_id']);
            $item->court?->append(['encrypted_id', 'image_path']);
            $item->court?->venueSport?->append('encrypted_id');
            $item->court?->venueSport?->venue?->append(['encrypted_id', 'image_path']);
            $item->court?->venueSport?->sport?->append(['encrypted_id', 'icon_path']);
        });

        return $group;
    }

    public static function createBooking($request, bool $isMultiple): array
    {
        $courtIds     = $request->court_id;
        $bookingDates = $request->booking_date;
        $startTimes   = $request->start_time;
        $endTimes     = $request->end_time;
        $participants = $request->participants;
        $notes        = $request->notes;

        // ── Resolve voucher (shared across all bookings) ──────────────────────
        $voucher   = null;
        $voucherId = null;

        if ($request->filled('voucher_code')) {
            $voucher = Voucher::where('code', $request->input('voucher_code'))
                ->where('status', 10)
                ->first();

            if (!$voucher) {
                return ['status' => 'error', 'message' => 'Voucher code is invalid or expired', 'code' => 422];
            }

            $voucherId = $voucher->id;
        }

        // ── Pre-validate every court before opening a transaction ─────────────
        $bookingItems = [];

        foreach ($courtIds as $i => $encryptedCourtId) {
            $courtId         = Helper::decode($encryptedCourtId);
            $court           = Court::where('status', 10)->with(['venueSport', 'pricings'])->find($courtId);
            $startTime       = Carbon::parse($startTimes[$i]);
            $endTime         = Carbon::parse($endTimes[$i]);
            $bookingDate     = $bookingDates[$i];
            $numParticipants = (int) ($participants[$i] ?? 1);

            if (!$court) {
                $label = $isMultiple ? ' (court #' . ($i + 1) . ')' : '';
                return ['status' => 'error', 'message' => "Court not found or unavailable{$label}", 'code' => 404];
            }

            $venueSport = $court->venueSport;

            // ── Operating hours ───────────────────────────────────────────────
            if ($venueSport) {
                $openTime  = Carbon::parse($venueSport->open_time);
                $closeTime = Carbon::parse($venueSport->close_time);

                if ($startTime->lt($openTime) || $endTime->gt($closeTime)) {
                    $label = $isMultiple ? ' (court #' . ($i + 1) . ')' : '';
                    return [
                        'status'  => 'error',
                        'message' => "Booking time must be within operating hours ({$venueSport->open_time} – {$venueSport->close_time}){$label}",
                        'code'    => 422,
                    ];
                }

                // ── Operating day ─────────────────────────────────────────────
                $dayOfWeek     = strtolower(Carbon::parse($bookingDate)->format('l'));
                $operatingDays = $venueSport->operating_days ?? [];

                if (!empty($operatingDays) && !in_array($dayOfWeek, $operatingDays)) {
                    $label = $isMultiple ? ' (court #' . ($i + 1) . ')' : '';
                    return [
                        'status'  => 'error',
                        'message' => 'Venue does not operate on ' . ucfirst($dayOfWeek) . $label,
                        'code'    => 422,
                    ];
                }
            }

            // ── Availability ──────────────────────────────────────────────────
            $isAvailable = CourtAvailabilityHelper::isCourtAvailable(
                $courtId, $bookingDate, $startTimes[$i], $endTimes[$i], null, $numParticipants
            );

            if (!$isAvailable) {
                $reason = CourtAvailabilityHelper::getUnavailabilityReason(
                    $courtId, $bookingDate, $startTimes[$i], $endTimes[$i]
                );
                $label = $isMultiple ? ' (court #' . ($i + 1) . ')' : '';
                return [
                    'status'  => 'error',
                    'message' => ($reason ?? 'Court is not available for the selected time slot') . $label,
                    'code'    => 422,
                ];
            }

            // ── Pricing ───────────────────────────────────────────────────────
            $slotDuration  = $venueSport ? (int) $venueSport->slot_duration : 60;
            $pricePerSlot  = $court->resolvePrice($startTimes[$i], $endTimes[$i], count($courtIds));
            $durationMins  = $endTime->diffInMinutes($startTime);
            $slots         = $slotDuration > 0 ? (int) floor($durationMins / $slotDuration) : 1;
            $durationHours = $endTime->diffInHours($startTime);
            $subtotal      = round($slots * $pricePerSlot, 2);

            $bookingItems[] = [
                'court'          => $court,
                'court_id'       => $courtId,
                'booking_date'   => $bookingDate,
                'start_time'     => $startTimes[$i],
                'end_time'       => $endTimes[$i],
                'duration_hours' => $durationHours,
                'participants'   => $numParticipants,
                'price_per_slot' => $pricePerSlot,
                'slots'          => $slots,
                'subtotal'       => $subtotal,
                'notes'          => $notes[$i] ?? null,
            ];
        }

        // ── Apply voucher to grand total, distribute proportionally ────────────
        $grandTotal    = array_sum(array_column($bookingItems, 'subtotal'));
        $totalDiscount = 0;

        if ($voucher) {
            if ($voucher->discount_type == 1) {       // percentage
                $totalDiscount = round($grandTotal * ($voucher->discount_amount / 100), 2);
            } elseif ($voucher->discount_type == 2) { // fixed amount
                $totalDiscount = min((float) $voucher->discount_amount, $grandTotal);
            }
        }

        $payableTotal = round($grandTotal - $totalDiscount, 2);

        // ── Prepare iPay88 credentials ────────────────────────────────────────
        $user              = auth()->user();
        $ipay88Credentials = Helper::getIPay88Credentials();
        $merchantCode      = $ipay88Credentials['merchant_code'];
        $merchantKey       = $ipay88Credentials['merchant_key'];
        $responseUrl       = $ipay88Credentials['response_url'];
        $backendUrl        = $ipay88Credentials['backend_url'];
        $currency          = $ipay88Credentials['currency'];

        $userName    = $user->fullname ?? $user->name;
        $userEmail   = $user->email;
        $userContact = ($user->calling_code ? '+' . $user->calling_code . ' ' : '') . $user->phone_number;

        // ── Create group + items in a single transaction ──────────────────────
        DB::beginTransaction();

        try {
            $remainingDiscount = $totalDiscount;
            $lastIndex         = count($bookingItems) - 1;

            // ── Create the master group ───────────────────────────────────────
            $paymentRef = self::generateBookingNumber();
            $prodDesc   = count($bookingItems) > 1
                ? 'Court Booking x' . count($bookingItems)
                : 'Court Booking - ' . $paymentRef;

            $group = CourtBookingGroup::create([
                'user_id'             => $user->id,
                'group_no'            => $paymentRef,
                'voucher_id'          => $voucherId,
                'subtotal'            => $grandTotal,
                'discount_amount'     => $totalDiscount,
                'total_amount'        => $payableTotal,
                'payment_status'      => 'pending',
                'payment_gateway'     => 'ipay88',
                'payment_gateway_ref' => $paymentRef,
                'payment_attempt'     => 1,
                'status'              => CourtBookingGroup::STATUS_PENDING_PAYMENT,
            ]);

            // ── Create court items ────────────────────────────────────────────
            $createdItems = [];

            foreach ($bookingItems as $idx => $item) {
                if ($idx === $lastIndex) {
                    $itemDiscount = $remainingDiscount;
                } elseif ($grandTotal > 0) {
                    $itemDiscount      = round($totalDiscount * ($item['subtotal'] / $grandTotal), 2);
                    $remainingDiscount -= $itemDiscount;
                } else {
                    $itemDiscount = 0;
                }

                $courtItem = CourtBooking::create([
                    'court_booking_group_id' => $group->id,
                    'court_id'               => $item['court_id'],
                    'booking_date'           => $item['booking_date'],
                    'start_time'             => $item['start_time'],
                    'end_time'               => $item['end_time'],
                    'duration_hours'         => $item['duration_hours'],
                    'participants'           => $item['participants'],
                    'price'                  => $item['price_per_slot'],
                    'total_amount'           => $item['subtotal'] - $itemDiscount,
                    'discount_amount'        => $itemDiscount,
                    'notes'                  => $item['notes'],
                ]);

                $createdItems[] = ['courtItem' => $courtItem, 'item' => $item];
            }

            // ── Generate iPay88 signature and payment data ────────────────────
            $signature   = self::generateIPay88Signature($merchantKey, $merchantCode, $paymentRef, $payableTotal, $currency);
            $paymentData = [
                'MerchantCode'  => $merchantCode,
                'PaymentId'     => '',
                'RefNo'         => $paymentRef,
                'Amount'        => number_format((float) $payableTotal, 2, '.', ''),
                'Currency'      => $currency,
                'ProdDesc'      => $prodDesc,
                'UserName'      => $userName,
                'UserEmail'     => $userEmail,
                'UserContact'   => $userContact,
                'Remark'        => '',
                'Lang'          => 'UTF-8',
                'Signature'     => $signature,
                'SignatureType' => 'HMACSHA512',
                'ResponseURL'   => $responseUrl,
                'BackendURL'    => $backendUrl,
            ];

            DB::commit();

        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

        $redirectUrl = Helper::getPaymentGatewayUrl() . '/api/v1/payment/ipay88/court-booking/' . $group->encrypted_id;

        // ── Build response payload ────────────────────────────────────────────
        $bookingPayload = array_map(function ($entry) {
            ['courtItem' => $courtItem, 'item' => $item] = $entry;
            return [
                'id'              => $courtItem->encrypted_id,
                'court'           => $item['court']->name,
                'booking_date'    => $courtItem->booking_date->format('Y-m-d'),
                'start_time'      => $courtItem->start_time,
                'end_time'        => $courtItem->end_time,
                'participants'    => $item['participants'],
                'slots'           => $item['slots'],
                'price_per_slot'  => $item['price_per_slot'],
                'total_amount'    => $courtItem->total_amount,
                'discount_amount' => $courtItem->discount_amount,
            ];
        }, $createdItems);

        $count = count($bookingPayload);
        $data  = $isMultiple
            ? [
                'group_id'       => $group->encrypted_id,
                'group_no'       => $group->group_no,
                'bookings'       => $bookingPayload,
                'grand_total'    => $payableTotal,
                'total_discount' => $totalDiscount,
                'status'         => $group->status_label,
                'receipt_url'    => self::receiptUrl($group->encrypted_id),
                'payment_url'    => $redirectUrl,
                'payment_data'   => $paymentData,
              ]
            : array_merge($bookingPayload[0], [
                'group_id'     => $group->encrypted_id,
                'group_no'     => $group->group_no,
                'status'       => $group->status_label,
                'receipt_url'  => self::receiptUrl($group->encrypted_id),
                'payment_url'  => $redirectUrl,
                'payment_data' => $paymentData,
              ]);

        return [
            'status'  => 'success',
            'message' => $count > 1 ? "{$count} bookings created successfully" : 'Booking created successfully',
            'data'    => $data,
        ];
    }

    public static function cancelUserBooking($request): array
    {
        $id    = Helper::decode($request->id);
        $group = CourtBookingGroup::where('user_id', auth()->user()->id)->find($id);

        if (!$group) {
            return ['status' => 'error', 'message' => 'Booking not found', 'code' => 404];
        }

        $cancellable = [CourtBookingGroup::STATUS_PENDING_PAYMENT, CourtBookingGroup::STATUS_UPCOMING];

        if (!in_array($group->status, $cancellable)) {
            return [
                'status'  => 'error',
                'message' => 'This booking cannot be cancelled (status: ' . $group->status_label . ')',
                'code'    => 422,
            ];
        }

        DB::beginTransaction();

        try {
            $group->status              = CourtBookingGroup::STATUS_CANCELED;
            $group->cancelled_at        = Carbon::now();
            $group->cancellation_reason = $request->cancellation_reason;
            $group->save();

            DB::commit();

        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

        return [
            'status'  => 'success',
            'message' => 'Booking cancelled successfully',
            'data'    => [
                'id'                  => $group->encrypted_id,
                'booking_no'          => $group->group_no,
                'status'              => $group->status_label,
                'cancellation_reason' => $group->cancellation_reason,
                'cancelled_at'        => $group->cancelled_at,
            ],
        ];
    }

    public static function getReceiptData($request): ?array
    {
        $id    = Helper::decode($request->id);
        $group = CourtBookingGroup::where('user_id', auth()->user()->id)
            ->with([
                'courtBookings:id,court_booking_group_id,court_id,booking_date,start_time,end_time,participants,price,total_amount,discount_amount',
                'courtBookings.court:id,venue_sport_id,name,capacity',
                'courtBookings.court.venueSport:id,venue_id,sport_id,slot_duration,price_per_slot',
                'courtBookings.court.venueSport.venue:id,name,address_1,address_2,city,state,postcode',
                'courtBookings.court.venueSport.sport:id,name',
                'user:id,fullname,email,calling_code,phone_number',
                'voucher:id,promo_code,discount_type,discount_amount',
            ])
            ->find($id);

        if (!$group) {
            return null;
        }

        $group->append('status_label');

        return ['group' => $group];
    }

    public static function receiptUrl(string $encryptedGroupId): string
    {
        return url('api/v1/court-bookings/receipt') . '?id=' . $encryptedGroupId;
    }

    private static function generateIPay88Signature($merchantKey, $merchantCode, $refNo, $amount, $currency): string
    {
        $amount = number_format((float) $amount, 2, '.', '');
        $amount = strtr($amount, ['.' => '', ',' => '']);
        $source = $merchantKey . $merchantCode . $refNo . $amount . $currency;
        return hash_hmac('sha512', $source, $merchantKey);
    }
}
