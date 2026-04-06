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
    Court,
    CourtBooking,
    CourtBookingGroup,
    CourtGallery,
    CourtPricing,
    FileManager,
    VenueSport,
};

use App\Helpers\CourtAvailabilityHelper;
use Carbon\Carbon;

class CourtService
{
    // ─── API Methods ──────────────────────────────────────────────────────────

    public static function getCourts($request)
    {
        $query = Court::where('status', 10)
            ->with([
                'venueSport.venue:id,name,address_1,city,state,latitude,longitude,image',
                'venueSport.sport:id,name,slug,icon',
                'pricings',
            ]);

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('venue_id')) {
            $venueId = Helper::decode($request->venue_id);
            $query->whereHas('venueSport', fn($q) => $q->where('venue_id', $venueId));
        }

        if ($request->filled('sport_id')) {
            $sportId = Helper::decode($request->sport_id);
            $query->whereHas('venueSport', fn($q) => $q->where('sport_id', $sportId));
        }

        $perPage = $request->input('per_page', 20);
        $courts  = $query->orderBy('name')->paginate($perPage);

        $courts->getCollection()->append(['encrypted_id', 'image_path']);
        $courts->getCollection()->each(function ($court) {
            if ($court->venueSport) {
                $court->venueSport->append('encrypted_id');
                $court->venueSport->venue?->append(['encrypted_id', 'image_path']);
                $court->venueSport->sport?->append(['encrypted_id', 'icon_path']);
            }
        });

        return $courts;
    }

    public static function getOneCourt($request)
    {
        $id    = Helper::decode($request->id);
        $court = Court::where('status', 10)
            ->with([
                'venueSport.venue:id,name,address_1,city,state,latitude,longitude,image',
                'venueSport.sport:id,name,slug,icon',
                'galleries:id,court_id,image',
                'pricings',
            ])
            ->find($id);

        if (!$court) {
            return null;
        }

        $court->append(['encrypted_id', 'image_path']);

        if ($court->venueSport) {
            $court->venueSport->append('encrypted_id');
            $court->venueSport->venue?->append(['encrypted_id', 'image_path']);
            $court->venueSport->sport?->append(['encrypted_id', 'icon_path']);
        }

        $court->galleries->each(fn($g) => $g->append('image_url'));

        return $court;
    }

    public static function getAvailableCourts($request)
    {
        $venueId = Helper::decode($request->venue_id);
        $sportId = Helper::decode($request->sport_id);

        $venueSport = VenueSport::where('venue_id', $venueId)
            ->where('sport_id', $sportId)
            ->where('status', 10)
            ->with(['venue:id,name', 'sport:id,name,slug,icon'])
            ->first();

        if (!$venueSport) {
            return ['status' => 'error', 'code' => 404, 'message' => 'Sport not offered at this venue'];
        }

        $durationHours = (float) $request->duration;
        $durationMins  = (int) round($durationHours * 60);

        $startTime    = Carbon::parse($request->start_time);
        $endTime      = $startTime->copy()->addMinutes($durationMins);
        $startTimeStr = $startTime->format('H:i');
        $endTimeStr   = $endTime->format('H:i');

        $openTime  = Carbon::parse($venueSport->open_time);
        $closeTime = Carbon::parse($venueSport->close_time);

        if ($startTime->lt($openTime)) {
            return ['status' => 'error', 'code' => 422, 'message' => 'Start time is before operating hours (' . $venueSport->open_time . ')'];
        }

        if ($endTime->gt($closeTime)) {
            return ['status' => 'error', 'code' => 422, 'message' => 'End time (' . $endTimeStr . ') exceeds operating hours (' . $venueSport->close_time . ')'];
        }

        $dayOfWeek = strtolower(Carbon::parse($request->date)->format('l'));

        if (!empty($venueSport->operating_days) && !in_array($dayOfWeek, $venueSport->operating_days)) {
            return ['status' => 'error', 'code' => 422, 'message' => 'Venue does not operate on ' . ucfirst($dayOfWeek)];
        }

        $slotDuration = (int) $venueSport->slot_duration;
        $slots        = $slotDuration > 0 ? (int) floor($durationMins / $slotDuration) : 1;
        $totalPrice   = round($slots * (float) $venueSport->price_per_slot, 2);

        $participants = (int) ($request->participants ?? 1);

        $courts = Court::where('venue_sport_id', $venueSport->id)
            ->where('status', 10)
            ->with('pricings')
            ->get();

        $availableCourts = $courts->filter(function ($court) use ($request, $startTimeStr, $endTimeStr, $participants) {
            return CourtAvailabilityHelper::isCourtAvailable(
                $court->id,
                $request->date,
                $startTimeStr,
                $endTimeStr,
                null,
                $participants
            );
        });

        $availableCourts->each(function ($c) use ($request, $startTimeStr, $endTimeStr) {
            $c->append(['encrypted_id', 'image_path']);
            $c->setAttribute('price_per_slot', $c->resolvePrice($startTimeStr, $endTimeStr));
            $capacity = (int) $c->capacity;
            if ($capacity > 1) {
                $booked = CourtBooking::where('court_id', $c->id)
                    ->whereHas('group', fn($q) => $q->whereIn('status', [
                        CourtBookingGroup::STATUS_PENDING_PAYMENT,
                        CourtBookingGroup::STATUS_UPCOMING,
                    ]))
                    ->where('booking_date', $request->date)
                    ->where('start_time', '<', $endTimeStr)
                    ->where('end_time',   '>', $startTimeStr)
                    ->sum('participants');
                $c->remaining_capacity = $capacity - (int) $booked;
                $c->total_capacity     = $capacity;
            }
        });

        $venueSport->sport?->append(['encrypted_id', 'icon_path']);

        return [
            'status' => 'success',
            'code'   => 200,
            'data'   => [
                'venue_sport' => [
                    'id'             => $venueSport->encrypted_id,
                    'slot_duration'  => $venueSport->slot_duration,
                    'price_per_slot' => $venueSport->price_per_slot,
                    'open_time'      => $venueSport->open_time,
                    'close_time'     => $venueSport->close_time,
                    'operating_days' => $venueSport->operating_days,
                    'sport'          => $venueSport->sport,
                ],
                'booking_summary' => [
                    'date'           => $request->date,
                    'day_of_week'    => $dayOfWeek,
                    'start_time'     => $startTimeStr,
                    'end_time'       => $endTimeStr,
                    'duration_hours' => $durationHours,
                    'slots'          => $slots,
                    'total_price'    => $totalPrice,
                ],
                'available_courts' => $availableCourts->values(),
                'total_available'  => $availableCourts->count(),
            ],
        ];
    }

    public static function getAvailableDates($request)
    {
        $venueId = Helper::decode($request->venue_id);
        $sportId = Helper::decode($request->sport_id);

        $venueSport = VenueSport::where('venue_id', $venueId)
            ->where('sport_id', $sportId)
            ->where('status', 10)
            ->first();

        if (!$venueSport) {
            return ['status' => 'error', 'code' => 404, 'message' => 'Sport not offered at this venue'];
        }

        $today = Carbon::today();

        if ($request->filled('month')) {
            $rangeStart = Carbon::createFromFormat('Y-m', $request->month)->startOfMonth()->startOfDay();
            $rangeEnd   = $rangeStart->copy()->endOfMonth()->startOfDay();
        } elseif ($request->filled('date_from')) {
            $rangeStart = Carbon::parse($request->date_from)->startOfDay();
            $rangeEnd   = $request->filled('date_to')
                ? Carbon::parse($request->date_to)->startOfDay()
                : $rangeStart->copy()->addDays(30);
        } else {
            $rangeStart = $today->copy();
            $rangeEnd   = $today->copy()->endOfMonth()->startOfDay();
        }

        if ($rangeStart->diffInDays($rangeEnd) > 90) {
            $rangeEnd = $rangeStart->copy()->addDays(90);
        }
        if ($rangeStart->lt($today)) {
            $rangeStart = $today->copy();
        }

        $slotDuration  = (int) $venueSport->slot_duration;
        $operatingDays = $venueSport->operating_days ?? [];
        $durationHours = $request->filled('duration') ? (float) $request->duration : $slotDuration / 60;
        $durationMins  = (int) round($durationHours * 60);

        $possibleSlots = self::generateTimeSlots(
            $venueSport->open_time,
            $venueSport->close_time,
            $slotDuration,
            $durationMins
        );

        if (empty($possibleSlots)) {
            return ['status' => 'error', 'code' => 422, 'message' => 'Requested duration exceeds operating hours'];
        }

        $courts = Court::where('venue_sport_id', $venueSport->id)
            ->where('status', 10)
            
            ->get(['id', 'capacity']);

        if ($courts->isEmpty()) {
            return ['status' => 'error', 'code' => 404, 'message' => 'No active courts found for this venue sport'];
        }

        $participants = (int) ($request->participants ?? 1);
        $courtIds     = $courts->pluck('id');

        $allBookings = CourtBooking::whereIn('court_id', $courtIds)
            ->whereBetween('booking_date', [
                $rangeStart->format('Y-m-d'),
                $rangeEnd->format('Y-m-d'),
            ])
            ->whereHas('group', fn($q) => $q->whereIn('status', [
                CourtBookingGroup::STATUS_PENDING_PAYMENT,
                CourtBookingGroup::STATUS_UPCOMING,
            ]))
            ->select('court_id', 'booking_date', 'start_time', 'end_time', 'participants')
            ->get()
            ->each(function ($b) {
                $b->start_time = Carbon::parse($b->start_time)->format('H:i');
                $b->end_time   = Carbon::parse($b->end_time)->format('H:i');
            })
            ->groupBy(fn($b) => Carbon::parse($b->booking_date)->format('Y-m-d'));

        $allCalendarBlocks = CourtAvailabilityHelper::getCalendarBlocksBulk(
            $courtIds->all(),
            $rangeStart->format('Y-m-d'),
            $rangeEnd->format('Y-m-d')
        );

        $capacityMap = $courts->pluck('capacity', 'id');

        $availableDates   = [];
        $unavailableDates = [];
        $current          = $rangeStart->copy();

        while ($current->lte($rangeEnd)) {
            $dateStr   = $current->format('Y-m-d');
            $dayOfWeek = strtolower($current->format('l'));

            if (!empty($operatingDays) && !in_array($dayOfWeek, $operatingDays)) {
                $unavailableDates[] = $dateStr;
                $current->addDay();
                continue;
            }

            $dateBookings    = $allBookings->get($dateStr, collect());
            $bookingsByCourt = $dateBookings->groupBy('court_id');
            $calByCourt      = $allCalendarBlocks->get($dateStr, collect());
            $hasAvailability = false;

            foreach ($possibleSlots as $slotStart) {
                $slotEnd = Carbon::parse($slotStart)->addMinutes($durationMins)->format('H:i');

                foreach ($courts as $court) {
                    $courtBookings  = $bookingsByCourt->get($court->id, collect());
                    $courtCalBlocks = $calByCourt->get($court->id, collect());
                    $capacity       = (int) ($capacityMap[$court->id] ?? 1);

                    $conflict = CourtAvailabilityHelper::isSlotConflicted(
                        $courtBookings,
                        $courtCalBlocks,
                        $slotStart,
                        $slotEnd,
                        $capacity,
                        $participants
                    );

                    if (!$conflict) {
                        $hasAvailability = true;
                        break 2;
                    }
                }
            }

            if ($hasAvailability) {
                $availableDates[] = $dateStr;
            } else {
                $unavailableDates[] = $dateStr;
            }

            $current->addDay();
        }

        return [
            'status' => 'success',
            'code'   => 200,
            'data'   => [
                'venue_sport' => [
                    'id'             => $venueSport->encrypted_id,
                    'slot_duration'  => $venueSport->slot_duration,
                    'price_per_slot' => $venueSport->price_per_slot,
                    'open_time'      => $venueSport->open_time,
                    'close_time'     => $venueSport->close_time,
                    'operating_days' => $operatingDays,
                ],
                'range' => [
                    'date_from'      => $rangeStart->format('Y-m-d'),
                    'date_to'        => $rangeEnd->format('Y-m-d'),
                    'duration_hours' => $durationHours,
                ],
                'available_dates'   => $availableDates,
                'unavailable_dates' => $unavailableDates,
            ],
        ];
    }

    public static function getAvailableTimes($request)
    {
        $venueId = Helper::decode($request->venue_id);
        $sportId = Helper::decode($request->sport_id);

        $venueSport = VenueSport::where('venue_id', $venueId)
            ->where('sport_id', $sportId)
            ->where('status', 10)
            ->first();

        if (!$venueSport) {
            return ['status' => 'error', 'code' => 404, 'message' => 'Sport not offered at this venue'];
        }

        $dayOfWeek     = strtolower(Carbon::parse($request->date)->format('l'));
        $operatingDays = $venueSport->operating_days ?? [];

        if (!empty($operatingDays) && !in_array($dayOfWeek, $operatingDays)) {
            return ['status' => 'error', 'code' => 422, 'message' => 'Venue does not operate on ' . ucfirst($dayOfWeek)];
        }

        $slotDuration  = (int) $venueSport->slot_duration;
        $durationHours = $request->filled('duration') ? (float) $request->duration : $slotDuration / 60;
        $durationMins  = (int) round($durationHours * 60);
        $slotsPerBook  = $slotDuration > 0 ? (int) floor($durationMins / $slotDuration) : 1;
        $totalPrice    = round($slotsPerBook * (float) $venueSport->price_per_slot, 2);

        $possibleSlots = self::generateTimeSlots(
            $venueSport->open_time,
            $venueSport->close_time,
            $slotDuration,
            $durationMins
        );

        if (empty($possibleSlots)) {
            return ['status' => 'error', 'code' => 422, 'message' => 'Requested duration exceeds operating hours'];
        }

        $courts = Court::where('venue_sport_id', $venueSport->id)
            ->where('status', 10)
            ->with('pricings')
            ->get();

        if ($courts->isEmpty()) {
            return ['status' => 'error', 'code' => 404, 'message' => 'No active courts found for this venue sport'];
        }

        $participants = (int) ($request->participants ?? 1);

        $bookingsByCourt = CourtBooking::whereIn('court_id', $courts->pluck('id'))
            ->where('booking_date', $request->date)
            ->whereHas('group', fn($q) => $q->whereIn('status', [
                CourtBookingGroup::STATUS_PENDING_PAYMENT,
                CourtBookingGroup::STATUS_UPCOMING,
            ]))
            ->select('court_id', 'start_time', 'end_time', 'participants')
            ->get()
            ->each(function ($b) {
                $b->start_time = Carbon::parse($b->start_time)->format('H:i');
                $b->end_time   = Carbon::parse($b->end_time)->format('H:i');
            })
            ->groupBy('court_id');

        $calendarBlocksByCourt = CourtAvailabilityHelper::getCalendarBlocksForDate(
            $courts->pluck('id')->all(),
            $request->date
        );

        $timeSlots = [];

        foreach ($possibleSlots as $slotStart) {
            $slotEnd           = Carbon::parse($slotStart)->addMinutes($durationMins)->format('H:i');
            $availableCourtIds = CourtAvailabilityHelper::getAvailableCourtIds(
                $courts,
                $bookingsByCourt,
                $calendarBlocksByCourt,
                $slotStart,
                $slotEnd,
                $participants
            );

            // Compute from_price: cheapest court × slots for this time window
            $availableCourts = $courts->filter(fn($c) => in_array($c->encrypted_id, $availableCourtIds));
            $fromPrice = $availableCourts->isNotEmpty()
                ? round($slotsPerBook * $availableCourts->min(fn($c) => $c->resolvePrice($slotStart, $slotEnd)), 2)
                : null;

            $timeSlots[] = [
                'start_time'             => $slotStart,
                'end_time'               => $slotEnd,
                'is_available'           => count($availableCourtIds) > 0,
                'available_courts_count' => count($availableCourtIds),
                'available_court_ids'    => $availableCourtIds,
                'total_price'            => $totalPrice,   // VenueSport base (backward compat)
                'from_price'             => $fromPrice,    // cheapest available court for this slot
            ];
        }

        return [
            'status' => 'success',
            'code'   => 200,
            'data'   => [
                'venue_sport' => [
                    'id'             => $venueSport->encrypted_id,
                    'slot_duration'  => $venueSport->slot_duration,
                    'price_per_slot' => $venueSport->price_per_slot,
                    'open_time'      => $venueSport->open_time,
                    'close_time'     => $venueSport->close_time,
                    'operating_days' => $operatingDays,
                ],
                'date'              => $request->date,
                'day_of_week'       => $dayOfWeek,
                'duration_hours'    => $durationHours,
                'slots_per_booking' => $slotsPerBook,
                'total_price'       => $totalPrice,
                'from_price'        => collect($timeSlots)->where('is_available', true)->min('from_price'),
                'total_courts'      => $courts->count(),
                'time_slots'        => $timeSlots,
            ],
        ];
    }

    private static function generateTimeSlots(
        string $openTime,
        string $closeTime,
        int    $slotDuration,
        int    $durationMins
    ): array {
        if ($slotDuration <= 0 || $durationMins <= 0) {
            return [];
        }

        $slots   = [];
        $current = Carbon::parse($openTime);
        $close   = Carbon::parse($closeTime);

        while ($current->copy()->addMinutes($durationMins)->lte($close)) {
            $slots[] = $current->format('H:i');
            $current->addMinutes($slotDuration);
        }

        return $slots;
    }

    // ─── Admin Methods ────────────────────────────────────────────────────────

    public static function bulkCreateCourts($request)
    {
        $validator = Validator::make($request->all(), [
            'venue_sport_id'   => ['required', 'exists:venue_sports,id'],
            'capacity'         => ['required', 'integer', 'min:1'],
            'price_per_hour'   => ['required', 'numeric', 'min:0'],
            'description'      => ['nullable', 'string'],
            'courts'           => ['required', 'array', 'min:1'],
            'courts.*.name'    => ['required', 'string', 'max:255'],
            'courts.*.slug'    => ['required', 'string', 'max:255'],
        ]);

        $validator->validate();

        $courts = $request->input('courts');

        // Unique slug check within the batch
        $batchSlugs = array_column($courts, 'slug');
        $dupeSlugs  = array_diff_assoc($batchSlugs, array_unique($batchSlugs));
        if (!empty($dupeSlugs)) {
            return response()->json([
                'errors' => ['courts' => 'Duplicate slugs in batch: ' . implode(', ', array_unique($dupeSlugs))],
            ], 422);
        }

        // Unique slug check against DB
        $existing = Court::whereIn('slug', $batchSlugs)->pluck('slug')->toArray();
        if (!empty($existing)) {
            return response()->json([
                'errors' => ['courts' => 'Slugs already taken: ' . implode(', ', $existing)],
            ], 422);
        }

        DB::beginTransaction();

        try {
            foreach ($courts as $court) {
                Court::create([
                    'venue_sport_id' => $request->venue_sport_id,
                    'name'           => $court['name'],
                    'slug'           => $court['slug'],
                    'description'    => $request->description,
                    'capacity'       => $request->capacity,
                    'price_per_hour' => $request->price_per_hour,
                    'status'         => 10,
                ]);
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage() . ' line: ' . $th->getLine()], 500);
        }

        $count = count($courts);

        return response()->json([
            'message' => "{$count} court" . ($count !== 1 ? 's' : '') . ' created successfully.',
        ]);
    }

    public static function createCourt($request)
    {
        $validator = Validator::make($request->all(), [
            'venue_sport_id'  => ['required', 'exists:venue_sports,id'],
            'name'            => ['required', 'string', 'max:255'],
            'slug'            => ['required', 'string', 'max:255', 'unique:courts,slug'],
            'description'     => ['nullable', 'string'],
            'capacity'        => ['required', 'integer', 'min:0'],
            'price_per_hour'  => ['required', 'numeric', 'min:0'],
            'image'           => ['nullable', 'mimes:jpeg,jpg,png', 'max:2048'],
            'gallery'         => ['nullable'],
        ]);

        $attributeName = [
            'venue_sport_id' => __('Sport'),
            'name'           => __('Name'),
            'slug'           => __('Slug'),
            'description'    => __('Description'),
            'capacity'       => __('Capacity'),
            'price_per_hour' => __('Price Per Hour'),
            'image'          => __('Image'),
            'gallery'        => __('Gallery'),
        ];

        foreach ($attributeName as $key => $aName) {
            $attributeName[$key] = strtolower($aName);
        }

        $validator->setAttributeNames($attributeName)->validate();

        DB::beginTransaction();

        try {
            $courtData = [
                'venue_sport_id' => $request->venue_sport_id,
                'name'           => $request->name,
                'slug'           => $request->slug,
                'description'    => $request->description,
                'capacity'       => $request->capacity,
                'price_per_hour' => $request->price_per_hour,
                'active'         => $request->has('active') ? true : false,
                'status'         => 10,
            ];

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('courts/images', ['disk' => 'public']);
                $courtData['image'] = $path;
            }

            $court = Court::create($courtData);

            self::syncCourtPricings($court, $request->input('pricing'));

            if (!empty($request->gallery)) {
                $galleryIds = array_filter(array_map('trim', explode(',', $request->gallery)));
                $galleryFiles = FileManager::whereIn('id', $galleryIds)->get();
                foreach ($galleryFiles as $galleryFile) {
                    $fileName = explode('/', $galleryFile->file);
                    $target = 'courts/' . $court->id . '/gallery/' . $fileName[1];
                    Storage::disk('public')->move($galleryFile->file, $target);
                    CourtGallery::create([
                        'court_id' => $court->id,
                        'image'    => $target,
                        'status'   => 10,
                    ]);
                    $galleryFile->status = 10;
                    $galleryFile->save();
                }
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500);
        }

        return response()->json([
            'message' => __('template.new_x_created', ['title' => 'Court']),
            'data' => [
                'id' => $court->id,
                'encrypted_id' => $court->encrypted_id,
            ],
            'status' => 200
        ]);
    }

    public static function updateCourt($request)
    {
        $request->merge([
            'id' => Helper::decode($request->id),
        ]);

        $validator = Validator::make($request->all(), [
            'id'             => ['required', 'exists:courts,id'],
            'venue_sport_id' => ['required', 'exists:venue_sports,id'],
            'name'           => ['required', 'string', 'max:255'],
            'slug'           => ['required', 'string', 'max:255', 'unique:courts,slug,' . $request->id],
            'description'    => ['nullable', 'string'],
            'capacity'       => ['required', 'integer', 'min:0'],
            'price_per_hour' => ['required', 'numeric', 'min:0'],
            'image'          => ['nullable', 'mimes:jpeg,jpg,png', 'max:2048'],
            'gallery'        => ['nullable'],
        ]);

        $attributeName = [
            'venue_sport_id' => __('Sport'),
            'name'           => __('Name'),
            'slug'           => __('Slug'),
            'description'    => __('Description'),
            'capacity'       => __('Capacity'),
            'price_per_hour' => __('Price Per Hour'),
            'image'          => __('Image'),
            'gallery'        => __('Gallery'),
        ];

        foreach ($attributeName as $key => $aName) {
            $attributeName[$key] = strtolower($aName);
        }

        $validator->setAttributeNames($attributeName)->validate();

        DB::beginTransaction();

        try {
            $court = Court::find($request->id);

            $court->venue_sport_id = $request->venue_sport_id;
            $court->name           = $request->name;
            $court->slug           = $request->slug;
            $court->description    = $request->description;
            $court->capacity       = $request->capacity;
            $court->price_per_hour = $request->price_per_hour;
            $court->active         = $request->has('active') ? true : false;

            if ($request->hasFile('image')) {
                if ($court->image) {
                    Storage::disk('public')->delete($court->image);
                }
                $path = $request->file('image')->store('courts/images', ['disk' => 'public']);
                $court->image = $path;
            }

            $court->save();

            self::syncCourtPricings($court, $request->input('pricing'));

            if (!empty($request->gallery)) {
                $galleryIds = array_filter(array_map('trim', explode(',', $request->gallery)));
                $galleryFiles = FileManager::whereIn('id', $galleryIds)->get();
                foreach ($galleryFiles as $galleryFile) {
                    $fileName = explode('/', $galleryFile->file);
                    $target = 'courts/' . $court->id . '/gallery/' . $fileName[1];
                    Storage::disk('public')->move($galleryFile->file, $target);
                    CourtGallery::create([
                        'court_id' => $court->id,
                        'image'    => $target,
                        'status'   => 10,
                    ]);
                    $galleryFile->status = 10;
                    $galleryFile->save();
                }
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500);
        }

        return response()->json([
            'message' => __('template.x_updated', ['title' => 'Court']),
        ]);
    }

    public static function allCourts($request)
    {
        $courts = Court::select('courts.*')
            ->with('venueSport.sport:id,name', 'venueSport.venue:id,name')
            ->withCount('pricings')
            ->orderBy('created_at', 'DESC');

        $filterObject = self::filter($request, $courts);
        $court = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ($request->input('order.0.column') != 0) {
            $dir = $request->input('order.0.dir');
            switch ($request->input('order.0.column')) {
                case 1:
                    $court->orderBy('courts.name', $dir);
                    break;
                case 2:
                    $court->orderBy('courts.location', $dir);
                    break;
                case 3:
                    $court->orderBy('courts.price_per_hour', $dir);
                    break;
                case 4:
                    $court->orderBy('courts.created_at', $dir);
                    break;
            }
        }

        $courtCount = $court->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $courts = $court->skip($offset)->take($limit)->get();

        if ($courts) {
            $courts->append([
                'encrypted_id',
                'image_path',
            ]);
        }

        $totalRecord = Court::count();

        $data = [
            'courts' => $courts,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $courtCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return response()->json($data);
    }

    public static function duplicateCourt($request)
    {
        $id    = Helper::decode($request->id);
        $court = Court::with(['pricings', 'galleries'])->find($id);

        if (!$court) {
            return response()->json(['message' => 'Court not found.'], 404);
        }

        DB::beginTransaction();

        try {
            // Build a unique slug
            $baseSlug = $court->slug;
            $slug     = $baseSlug . '-copy';
            $counter  = 1;
            while (Court::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-copy-' . $counter++;
            }

            $newCourt = Court::create([
                'venue_sport_id' => $court->venue_sport_id,
                'name'           => $court->name . ' (Copy)',
                'slug'           => $slug,
                'description'    => $court->description,
                'capacity'       => $court->capacity,
                'price_per_hour' => $court->price_per_hour,
                'image'          => null,
                'active'         => false,
                'status'         => 20, // inactive by default
            ]);

            // Copy image
            if ($court->image && Storage::disk('public')->exists($court->image)) {
                $ext      = pathinfo($court->image, PATHINFO_EXTENSION);
                $newImage = 'courts/images/' . Str::uuid() . '.' . $ext;
                Storage::disk('public')->copy($court->image, $newImage);
                $newCourt->image = $newImage;
                $newCourt->save();
            }

            // Copy pricing tiers
            foreach ($court->pricings as $tier) {
                $newCourt->pricings()->create([
                    'label'      => $tier->label,
                    'time_from'  => $tier->time_from,
                    'time_to'    => $tier->time_to,
                    'min_courts' => $tier->min_courts,
                    'price'      => $tier->price,
                ]);
            }

            // Copy gallery images
            foreach ($court->galleries as $gallery) {
                if (Storage::disk('public')->exists($gallery->image)) {
                    $ext      = pathinfo($gallery->image, PATHINFO_EXTENSION);
                    $newPath  = 'courts/' . $newCourt->id . '/gallery/' . Str::uuid() . '.' . $ext;
                    Storage::disk('public')->copy($gallery->image, $newPath);
                    CourtGallery::create([
                        'court_id' => $newCourt->id,
                        'image'    => $newPath,
                        'status'   => $gallery->status,
                    ]);
                }
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500);
        }

        return response()->json([
            'message'      => 'Court duplicated successfully.',
            'encrypted_id' => $newCourt->encrypted_id,
        ]);
    }

    private static function syncCourtPricings(Court $court, ?string $pricingJson): void
    {
        $court->pricings()->delete();

        $tiers = $pricingJson ? json_decode($pricingJson, true) : [];

        if (!is_array($tiers)) {
            return;
        }

        foreach ($tiers as $tier) {
            $price = isset($tier['price']) && $tier['price'] !== '' ? $tier['price'] : null;

            if ($price === null) {
                continue;
            }

            $court->pricings()->create([
                'label'      => $tier['label'] ?? null,
                'time_from'  => ($tier['time_from'] ?? '') ?: null,
                'time_to'    => ($tier['time_to'] ?? '') ?: null,
                'min_courts' => max(1, intval($tier['min_courts'] ?? 1)),
                'price'      => $price,
            ]);
        }
    }

    private static function filter($request, $model)
    {
        $filter = false;

        if (!empty($request->name)) {
            $model->where('courts.name', 'LIKE', '%' . $request->name . '%');
            $filter = true;
        }

        if (!empty($request->slug)) {
            $model->where('courts.slug', 'LIKE', '%' . $request->slug . '%');
            $filter = true;
        }

        if (!empty($request->location)) {
            $model->where('courts.location', 'LIKE', '%' . $request->location . '%');
            $filter = true;
        }

        if (!empty($request->sport_id)) {
            $model->where('courts.sport_id', $request->sport_id);
            $filter = true;
        }

        if (!empty($request->venue)) {
            $model->whereHas('venueSport.venue', function ($query) use ($request) {
                $query->where('name', 'LIKE', '%' . $request->venue . '%');
            });
            $filter = true;
        }

        if (!empty($request->sport)) {
            $model->whereHas('venueSport.sport', function ($query) use ($request) {
                $query->where('name', 'LIKE', '%' . $request->sport . '%');
            });
            $filter = true;
        }

        if (!empty($request->status)) {
            $model->where('courts.status', $request->status);
            $filter = true;
        }

        if (!empty($request->custom_search)) {
            $model->where(function ($query) use ($request) {
                $query->where('courts.name', 'LIKE', '%' . $request->custom_search . '%')
                    ->orWhere('courts.slug', 'LIKE', '%' . $request->custom_search . '%')
                    ->orWhere('courts.location', 'LIKE', '%' . $request->custom_search . '%');
            });
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneCourt($request)
    {
        $request->merge([
            'id' => Helper::decode($request->id),
        ]);

        $court = Court::with(['venueSport.venue', 'venueSport.sport', 'galleries', 'pricings'])->find($request->id);

        $court->append(['encrypted_id', 'image_path']);

        if ($court->galleries) {
            $court->galleries->each->append(['image_url']);
        }

        return response()->json($court);
    }

    public static function deleteCourt($request)
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
            $court = Court::find($request->id);

            if ($court->image) {
                Storage::disk('public')->delete($court->image);
            }

            foreach ($court->galleries as $gallery) {
                if ($gallery->image) {
                    Storage::disk('public')->delete($gallery->image);
                }
            }

            $court->delete();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500);
        }

        return response()->json([
            'message' => __('template.x_deleted', ['title' => 'Court']),
        ]);
    }

    public static function updateCourtStatus($request)
    {
        $request->merge([
            'id' => Helper::decode($request->id),
        ]);

        DB::beginTransaction();

        try {
            $court = Court::find($request->id);
            $court->status = $court->status == 10 ? 20 : 10;

            $court->save();
            DB::commit();

            $statusText = $court->status == 10 ? __('datatables.activated') : __('datatables.suspended');

            return response()->json([
                'message' => __('template.x_updated', ['title' => 'Court']) . ' - ' . $statusText,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500);
        }
    }

    public static function removeCourtImage($request)
    {
        $request->merge([
            'id' => Helper::decode($request->id),
        ]);

        $court = Court::find($request->id);

        if ($court->image) {
            Storage::disk('public')->delete($court->image);
            $court->image = null;
            $court->save();
        }

        return response()->json([
            'message' => __('template.x_updated', ['title' => 'Image']),
        ]);
    }

    public static function removeCourtGalleryImage($request)
    {
        $gallery = CourtGallery::find($request->gallery_id);

        if (!$gallery) {
            return response()->json(['message' => 'Gallery image not found.'], 404);
        }

        if ($gallery->image) {
            Storage::disk('public')->delete($gallery->image);
        }

        $gallery->delete();

        return response()->json([
            'message' => __('template.x_updated', ['title' => 'Gallery']),
        ]);
    }
}
