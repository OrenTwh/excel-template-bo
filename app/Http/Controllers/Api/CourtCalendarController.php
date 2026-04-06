<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Validator};
use App\Models\{CourtCalendar, CourtCalendarParticipant, Court};
use App\Helpers\CourtAvailabilityHelper;
use Carbon\Carbon;
use Helper;

class CourtCalendarController extends Controller
{
    /**
     * Get Court Calendar Slots
     *
     * Returns available time slots for a court within a date range.
     *
     * @group Court Calendar API
     *
     * @queryParam court_id    string required Encrypted court ID. Example: E2
     * @queryParam date_from   string required Start date (Y-m-d). Example: 2026-03-05
     * @queryParam date_to     string optional End date (Y-m-d), defaults to date_from. Example: 2026-03-10
     * @queryParam available_only integer optional Return only available slots (1 = yes). Example: 1
     *
     */
    public function getSlots(Request $request)
    {
        try {
            $courtId = Helper::decode($request->court_id);
            $court   = Court::where('status', 10)->with('pricings')->find($courtId);

            if (!$court) {
                return response()->json(['status' => 'error', 'message' => 'Court not found'], 404);
            }

            $dateFrom = $request->date_from;
            $dateTo   = $request->date_to ?? $dateFrom;

            $query = CourtCalendar::where('court_id', $courtId)
                ->where('status', 10)
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->orderBy('date')
                ->orderBy('start_time');

            if ($request->available_only == 1) {
                $query->where('is_available', true);
            }

            $slots = $query->get()->append('encrypted_id');

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'court' => [
                        'id'         => $court->encrypted_id,
                        'name'       => $court->name,
                        'base_price' => $court->price_per_hour,
                        'pricings'   => $court->pricings,
                    ],
                    'date_from' => $dateFrom,
                    'date_to'   => $dateTo,
                    'slots'     => $slots,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to retrieve court calendar', 'debug' => $e->getMessage()], 500);
        }
    }

    /**
     * Get Court Availability for a Specific Date
     *
     * @group Court Calendar API
     *
     * @queryParam court_id string required Encrypted court ID. Example: E2
     * @queryParam date     string required Date to check (Y-m-d). Example: 2026-03-05
     *
     */
    public function getAvailability(Request $request)
    {
        try {
            $courtId = Helper::decode($request->court_id);
            $court   = Court::where('status', 10)->with('pricings')->find($courtId);

            if (!$court) {
                return response()->json(['status' => 'error', 'message' => 'Court not found'], 404);
            }

            $date      = Carbon::parse($request->date);
            $dayOfWeek = strtolower($date->format('l'));

            $slots = CourtCalendar::where('court_id', $courtId)
                ->where('status', 10)
                ->where(function ($q) use ($request, $dayOfWeek) {
                    $q->whereDate('date', $request->date)
                      ->orWhere(function ($q2) use ($dayOfWeek) {
                          $q2->where('is_recurring', true)->where('day_of_week', $dayOfWeek);
                      });
                })
                ->orderBy('start_time')
                ->get()
                ->append('encrypted_id')
                ->map(function ($slot) use ($court) {
                    return [
                        'id'                    => $slot->encrypted_id,
                        'date'                  => $slot->date->format('Y-m-d'),
                        'day_of_week'           => $slot->day_of_week,
                        'start_time'            => $slot->start_time,
                        'end_time'              => $slot->end_time,
                        'is_available'          => $slot->is_available,
                        'is_recurring'          => $slot->is_recurring,
                        'unavailability_reason' => $slot->unavailability_reason,
                        'price'                 => $slot->special_price ?? $court->resolvePrice($slot->start_time, $slot->end_time),
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'court'       => [
                        'id'         => $court->encrypted_id,
                        'name'       => $court->name,
                        'base_price' => $court->price_per_hour,
                        'pricings'   => $court->pricings,
                    ],
                    'date'        => $request->date,
                    'day_of_week' => $dayOfWeek,
                    'slots'       => $slots,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to retrieve court availability', 'debug' => $e->getMessage()], 500);
        }
    }

    // ── Event endpoints ───────────────────────────────────────────────────────

    /**
     * Get Upcoming Events
     *
     * Returns paginated list of hosted events that users can join.
     *
     * @group Court Calendar Events API
     *
     * @queryParam sport_id  string  Filter by encrypted sport ID.
     * @queryParam venue_id  string  Filter by encrypted venue ID.
     * @queryParam date_from string  Filter from date (Y-m-d). Example: 2026-03-10
     * @queryParam date_to   string  Filter to date (Y-m-d). Example: 2026-04-10
     * @queryParam per_page  integer Records per page, default 15. Example: 15
     *
     */
    public function getEvents(Request $request)
    {
        try {
            $query = CourtCalendar::where('is_event', true)
                ->where('status', 10)
                ->where('created_by_user_id', null)
                ->where('is_available', true)
                ->where('date', '>=', Carbon::today()->toDateString())
                ->with([
                    'court:id,venue_sport_id,name,image',
                    'court.venueSport.venue:id,name,address_1,city,state,image',
                    'court.venueSport.sport:id,name,slug,icon',
                    'confirmedParticipants.user:id,email,profile_picture',
                ])
                ->withCount(['confirmedParticipants as participants_count']);

            if ($request->filled('sport_id')) {
                $sportId = Helper::decode($request->sport_id);
                $query->whereHas('court.venueSport', fn($q) => $q->where('sport_id', $sportId));
            }

            if ($request->filled('venue_id')) {
                $venueId = Helper::decode($request->venue_id);
                $query->whereHas('court.venueSport', fn($q) => $q->where('venue_id', $venueId));
            }

            if ($request->filled('date_from')) {
                $query->where('date', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->where('date', '<=', $request->date_to);
            }

            $perPage = $request->input('per_page', 15);
            $events  = $query->orderBy('date')->orderBy('start_time')->paginate($perPage);

            $events->getCollection()->transform(function ($event) {
                $event->append('encrypted_id');
                $availableSpots = is_null($event->max_participants)
                    ? null
                    : max(0, $event->max_participants - $event->participants_count);

                return [
                    'id'                    => $event->encrypted_id,
                    'event_title'           => $event->event_title,
                    'event_description'     => $event->event_description,
                    'date'                  => $event->date->format('Y-m-d'),
                    'day_of_week'           => $event->day_of_week,
                    'start_time'            => $event->start_time,
                    'end_time'              => $event->end_time,
                    'max_participants'      => $event->max_participants,
                    'participants_count'    => $event->participants_count,
                    'available_spots'       => $availableSpots,
                    'is_full'               => !is_null($availableSpots) && $availableSpots === 0,
                    'price_per_participant' => $event->price_per_participant,
                    'court'                 => $event->court ? [
                        'id'    => $event->court->encrypted_id,
                        'name'  => $event->court->name,
                        'image' => $event->court->image_path,
                    ] : null,
                    'venue' => $event->court?->venueSport?->venue ? [
                        'id'      => $event->court->venueSport->venue->encrypted_id,
                        'name'    => $event->court->venueSport->venue->name,
                        'address' => $event->court->venueSport->venue->address_1,
                        'city'    => $event->court->venueSport->venue->city,
                        'state'   => $event->court->venueSport->venue->state,
                        'image'   => $event->court->venueSport->venue->image_path,
                    ] : null,
                    'sport' => $event->court?->venueSport?->sport ? [
                        'id'   => $event->court->venueSport->sport->encrypted_id,
                        'name' => $event->court->venueSport->sport->name,
                        'icon' => $event->court->venueSport->sport->icon_path,
                    ] : null,
                    'attendees' => $event->confirmedParticipants->map(fn($p) => $p->user ? [
                        'email'               => $p->user->email,
                        'profile_picture_path' => $p->user->profile_picture_path_new,
                    ] : null)->filter()->values(),
                ];
            });

            return response()->json([
                'status' => 'success',
                'data'   => $events,
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to retrieve events', 'debug' => $e->getMessage()], 500);
        }
    }

    /**
     * Get Event Details
     *
     * Returns full details of a single hosted event.
     *
     * @group Court Calendar Events API
     *
     * @queryParam id string required Encrypted calendar event ID. Example: E2
     *
     */
    public function getEventDetails(Request $request)
    {
        try {
            $id    = Helper::decode($request->id);
            $event = CourtCalendar::where('is_event', true)
                ->where('status', 10)
                ->with([
                    'court:id,venue_sport_id,name,capacity,image',
                    'court.venueSport.venue:id,name,address_1,address_2,city,state,postcode,gmap_link,waze_link,image',
                    'court.venueSport.sport:id,name,slug,icon',
                    'confirmedParticipants.user:id,email,profile_picture',
                ])
                ->withCount(['confirmedParticipants as participants_count'])
                ->find($id);

            if (!$event) {
                return response()->json(['status' => 'error', 'message' => 'Event not found'], 404);
            }

            $event->append('encrypted_id');
            $availableSpots = is_null($event->max_participants)
                ? null
                : max(0, $event->max_participants - $event->participants_count);

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'id'                    => $event->encrypted_id,
                    'event_title'           => $event->event_title,
                    'event_description'     => $event->event_description,
                    'date'                  => $event->date->format('Y-m-d'),
                    'day_of_week'           => $event->day_of_week,
                    'start_time'            => $event->start_time,
                    'end_time'              => $event->end_time,
                    'max_participants'      => $event->max_participants,
                    'participants_count'    => $event->participants_count,
                    'available_spots'       => $availableSpots,
                    'is_full'               => !is_null($availableSpots) && $availableSpots === 0,
                    'price_per_participant' => $event->price_per_participant,
                    'court' => $event->court ? [
                        'id'       => $event->court->encrypted_id,
                        'name'     => $event->court->name,
                        'capacity' => $event->court->capacity,
                        'image'    => $event->court->image_path,
                    ] : null,
                    'venue' => $event->court?->venueSport?->venue ? [
                        'id'        => $event->court->venueSport->venue->encrypted_id,
                        'name'      => $event->court->venueSport->venue->name,
                        'address_1' => $event->court->venueSport->venue->address_1,
                        'address_2' => $event->court->venueSport->venue->address_2,
                        'city'      => $event->court->venueSport->venue->city,
                        'state'     => $event->court->venueSport->venue->state,
                        'postcode'  => $event->court->venueSport->venue->postcode,
                        'gmap_link' => $event->court->venueSport->venue->gmap_link,
                        'waze_link' => $event->court->venueSport->venue->waze_link,
                        'image'     => $event->court->venueSport->venue->image_path,
                    ] : null,
                    'sport' => $event->court?->venueSport?->sport ? [
                        'id'   => $event->court->venueSport->sport->encrypted_id,
                        'name' => $event->court->venueSport->sport->name,
                        'icon' => $event->court->venueSport->sport->icon_path,
                    ] : null,
                    'attendees' => $event->confirmedParticipants->map(fn($p) => $p->user ? [
                        'email'                => $p->user->email,
                        'profile_picture_path' => $p->user->profile_picture_path_new,
                    ] : null)->filter()->values(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to retrieve event details', 'debug' => $e->getMessage()], 500);
        }
    }

    /**
     * Join Event
     *
     * Register the authenticated user to a hosted event.
     *
     * @authenticated
     * @group Court Calendar Events API
     *
     * @bodyParam id string required Encrypted calendar event ID. Example: E2
     *
     */
    public function joinEvent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $id    = Helper::decode($request->id);
            $event = CourtCalendar::where('is_event', true)
                ->where('status', 10)
                ->where('is_available', true)
                ->where('date', '>=', Carbon::today()->toDateString())
                ->withCount(['confirmedParticipants as participants_count'])
                ->find($id);

            if (!$event) {
                return response()->json(['status' => 'error', 'message' => 'Event not found or no longer available'], 404);
            }

            // Check if already joined (any status)
            $existing = CourtCalendarParticipant::where('court_calendar_id', $id)
                ->where('user_id', auth()->user()->id)
                ->first();

            if ($existing) {
                if ($existing->status === CourtCalendarParticipant::STATUS_CONFIRMED) {
                    return response()->json(['status' => 'error', 'message' => 'You have already joined this event'], 422);
                }

                // Re-join if previously cancelled
                DB::beginTransaction();
                $existing->status         = CourtCalendarParticipant::STATUS_CONFIRMED;
                $existing->joined_at      = Carbon::now();
                $existing->cancelled_at   = null;
                $existing->cancellation_reason = null;
                $existing->save();
                DB::commit();

                return response()->json([
                    'status'  => 'success',
                    'message' => 'Successfully joined the event',
                    'data'    => $this->participantResponse($existing, $event),
                ]);
            }

            // Check capacity
            if (!is_null($event->max_participants) && $event->participants_count >= $event->max_participants) {
                return response()->json(['status' => 'error', 'message' => 'This event is full'], 422);
            }

            DB::beginTransaction();

            $participant = CourtCalendarParticipant::create([
                'court_calendar_id' => $id,
                'user_id'           => auth()->user()->id,
                'status'            => CourtCalendarParticipant::STATUS_CONFIRMED,
                'joined_at'         => Carbon::now(),
            ]);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Successfully joined the event',
                'data'    => $this->participantResponse($participant, $event),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Failed to join event', 'debug' => $e->getMessage(), 'debug' => $e->getMessage()], 500);
        }
    }

    /**
     * Leave Event
     *
     * Cancel the authenticated user's registration from a hosted event.
     *
     * @authenticated
     * @group Court Calendar Events API
     *
     * @bodyParam id                  string required Encrypted calendar event ID. Example: E2
     * @bodyParam cancellation_reason string optional Reason for leaving. Example: Change of plans.
     *
     */
    public function leaveEvent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'                  => ['required'],
            'cancellation_reason' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $id          = Helper::decode($request->id);
            $participant = CourtCalendarParticipant::where('court_calendar_id', $id)
                ->where('user_id', auth()->user()->id)
                ->where('status', CourtCalendarParticipant::STATUS_CONFIRMED)
                ->first();

            if (!$participant) {
                return response()->json(['status' => 'error', 'message' => 'You are not registered for this event'], 404);
            }

            // Prevent leaving a past event
            $event = CourtCalendar::find($id);
            if ($event && $event->date < Carbon::today()) {
                return response()->json(['status' => 'error', 'message' => 'Cannot leave a past event'], 422);
            }

            DB::beginTransaction();

            $participant->status               = CourtCalendarParticipant::STATUS_CANCELLED;
            $participant->cancelled_at         = Carbon::now();
            $participant->cancellation_reason  = $request->cancellation_reason;
            $participant->save();

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Successfully left the event',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Failed to leave event', 'debug' => $e->getMessage()], 500);
        }
    }

    /**
     * Get My Events
     *
     * Returns the authenticated user's event registrations.
     *
     * @authenticated
     * @group Court Calendar Events API
     *
     * @queryParam status   integer Filter by status (10=confirmed, 20=cancelled). Example: 10
     * @queryParam per_page integer Records per page, default 15. Example: 15
     *
     */
    public function getMyEvents(Request $request)
    {
        try {
            $query = CourtCalendarParticipant::where('user_id', auth()->user()->id)
                ->with([
                    'courtCalendar:id,court_id,date,day_of_week,start_time,end_time,event_title,event_description,max_participants,price_per_participant,is_available',
                    'courtCalendar.court:id,venue_sport_id,name,image',
                    'courtCalendar.court.venueSport.venue:id,name,address_1,city,state,image',
                    'courtCalendar.court.venueSport.sport:id,name,slug,icon',
                ]);

            if ($request->filled('status')) {
                $query->where('status', (int) $request->status);
            }

            $perPage       = $request->input('per_page', 15);
            $participations = $query->orderBy('joined_at', 'desc')->paginate($perPage);

            $participations->getCollection()->transform(function ($p) {
                $event = $p->courtCalendar;
                $p->append('encrypted_id');
                $event?->append('encrypted_id');

                return [
                    'id'                  => $p->encrypted_id,
                    'status'              => $p->status_label,
                    'joined_at'           => $p->joined_at,
                    'cancelled_at'        => $p->cancelled_at,
                    'cancellation_reason' => $p->cancellation_reason,
                    'event' => $event ? [
                        'id'                    => $event->encrypted_id,
                        'event_title'           => $event->event_title,
                        'event_description'     => $event->event_description,
                        'date'                  => $event->date?->format('Y-m-d'),
                        'day_of_week'           => $event->day_of_week,
                        'start_time'            => $event->start_time,
                        'end_time'              => $event->end_time,
                        'max_participants'      => $event->max_participants,
                        'price_per_participant' => $event->price_per_participant,
                        'court' => $event->court ? [
                            'id'    => $event->court->encrypted_id,
                            'name'  => $event->court->name,
                            'image' => $event->court->image_path,
                        ] : null,
                        'venue' => $event->court?->venueSport?->venue ? [
                            'id'    => $event->court->venueSport->venue->encrypted_id,
                            'name'  => $event->court->venueSport->venue->name,
                            'city'  => $event->court->venueSport->venue->city,
                            'state' => $event->court->venueSport->venue->state,
                            'image' => $event->court->venueSport->venue->image_path,
                        ] : null,
                        'sport' => $event->court?->venueSport?->sport ? [
                            'id'   => $event->court->venueSport->sport->encrypted_id,
                            'name' => $event->court->venueSport->sport->name,
                            'icon' => $event->court->venueSport->sport->icon_path,
                        ] : null,
                    ] : null,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data'   => $participations,
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to retrieve events', 'debug' => $e->getMessage()], 500);
        }
    }

    // ── User-created activity endpoints ──────────────────────────────────────

    /**
     * Create User Activity
     *
     * Allows an authenticated user to create a group activity on a court
     * for other users to join. The slot is automatically blocked from regular booking.
     *
     * @authenticated
     * @group User Activities API
     *
     * @bodyParam court_id             string  required Encrypted court ID. Example: E2
     * @bodyParam date                 string  required Activity date (Y-m-d). Example: 2026-04-01
     * @bodyParam start_time           string  required Start time (H:i). Example: 09:00
     * @bodyParam end_time             string  required End time (H:i). Example: 11:00
     * @bodyParam event_title          string  required Activity title. Example: Badminton Session
     * @bodyParam event_description    string  optional Description. Example: Casual doubles game.
     * @bodyParam max_participants     integer optional Max number of participants (excluding creator). Example: 4
     * @bodyParam price_per_participant decimal optional Cost per participant. Example: 10.00
     *
     */
    public function createUserActivity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'court_id'              => ['required'],
            'date'                  => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'start_time'            => ['required', 'date_format:H:i'],
            'end_time'              => ['required', 'date_format:H:i', 'after:start_time'],
            'event_title'           => ['required', 'string', 'max:255'],
            'event_description'     => ['nullable', 'string'],
            'max_participants'      => ['nullable', 'integer', 'min:1'],
            'price_per_participant' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $courtId = Helper::decode($request->court_id);
            $court   = Court::where('status', 10)->find($courtId);

            if (!$court) {
                return response()->json(['status' => 'error', 'message' => 'Court not found'], 404);
            }

            // Check the slot is actually free
            if (!CourtAvailabilityHelper::isCourtAvailable($courtId, $request->date, $request->start_time, $request->end_time)) {
                $reason = CourtAvailabilityHelper::getUnavailabilityReason($courtId, $request->date, $request->start_time, $request->end_time);
                return response()->json([
                    'status'  => 'error',
                    'message' => $reason ?? 'This time slot is not available',
                ], 422);
            }

            DB::beginTransaction();

            $activity = CourtCalendar::create([
                'court_id'              => $courtId,
                'date'                  => $request->date,
                'day_of_week'           => strtolower(Carbon::parse($request->date)->format('l')),
                'start_time'            => $request->start_time,
                'end_time'              => $request->end_time,
                'is_available'          => true,   // Joinable
                'is_recurring'          => false,
                'is_event'              => true,   // Blocks slot from regular booking via helper
                'event_title'           => $request->event_title,
                'event_description'     => $request->event_description,
                'max_participants'      => $request->max_participants,
                'price_per_participant' => $request->price_per_participant,
                'status'                => 10,
                'created_by_user_id'    => auth()->user()->id,
            ]);

            // Auto-join the creator as a confirmed participant
            CourtCalendarParticipant::create([
                'court_calendar_id' => $activity->id,
                'user_id'           => auth()->user()->id,
                'status'            => CourtCalendarParticipant::STATUS_CONFIRMED,
                'joined_at'         => Carbon::now(),
            ]);

            DB::commit();

            $activity->append('encrypted_id');

            return response()->json([
                'status'  => 'success',
                'message' => 'Activity created successfully',
                'data'    => $this->activityResponse($activity),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Failed to create activity', 'debug' => $e->getMessage()], 500);
        }
    }

    /**
     * Get User Activities
     *
     * Returns paginated list of user-created activities available to join.
     *
     * @group User Activities API
     *
     * @queryParam court_id  string  Filter by encrypted court ID. Example: E2
     * @queryParam sport_id  string  Filter by encrypted sport ID. Example: E2
     * @queryParam venue_id  string  Filter by encrypted venue ID. Example: E2
     * @queryParam date_from string  Filter from date (Y-m-d). Example: 2026-04-01
     * @queryParam date_to   string  Filter to date (Y-m-d). Example: 2026-04-30
     * @queryParam per_page  integer Records per page, default 15. Example: 15
     *
     */
    public function getUserActivities(Request $request)
    {
        try {
            $query = CourtCalendar::userActivities()
                ->whereNotNull('created_by_user_id')
                ->where('status', 10)
                ->where('is_available', true)
                ->where('date', '>=', Carbon::today()->toDateString())
                ->with([
                    'court:id,venue_sport_id,name,image',
                    'court.venueSport.venue:id,name,address_1,city,state,image',
                    'court.venueSport.sport:id,name,slug,icon',
                    'createdBy:id,email,profile_picture',
                    'confirmedParticipants.user:id,email,profile_picture',
                ])
                ->withCount(['confirmedParticipants as participants_count']);

            if ($request->filled('court_id')) {
                $query->where('court_id', Helper::decode($request->court_id));
            }

            if ($request->filled('sport_id')) {
                $sportId = Helper::decode($request->sport_id);
                $query->whereHas('court.venueSport', fn($q) => $q->where('sport_id', $sportId));
            }

            if ($request->filled('venue_id')) {
                $venueId = Helper::decode($request->venue_id);
                $query->whereHas('court.venueSport', fn($q) => $q->where('venue_id', $venueId));
            }

            if ($request->filled('date_from')) {
                $query->where('date', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->where('date', '<=', $request->date_to);
            }

            $perPage    = $request->input('per_page', 15);
            $activities = $query->orderBy('date')->orderBy('start_time')->paginate($perPage);

            $activities->getCollection()->transform(fn($a) => $this->activityResponse($a));

            return response()->json([
                'status' => 'success',
                'data'   => $activities,
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to retrieve activities', 'debug' => $e->getMessage()], 500);
        }
    }

    /**
     * Get Activity Details
     *
     * @group User Activities API
     *
     * @queryParam id string required Encrypted activity ID. Example: E2
     *
     */
    public function getUserActivityDetails(Request $request)
    {
        try {
            $id       = Helper::decode($request->id);
            $activity = CourtCalendar::userActivities()
                ->where('is_event', true)
                ->where('status', 10)
                ->with([
                    'court:id,venue_sport_id,name,capacity,image',
                    'court.venueSport.venue:id,name,address_1,address_2,city,state,postcode,gmap_link,waze_link,image',
                    'court.venueSport.sport:id,name,slug,icon',
                    'createdBy:id,email,profile_picture',
                    'confirmedParticipants.user:id,email,profile_picture',
                ])
                ->withCount(['confirmedParticipants as participants_count'])
                ->find($id);

            if (!$activity) {
                return response()->json(['status' => 'error', 'message' => 'Activity not found'], 404);
            }

            return response()->json([
                'status' => 'success',
                'data'   => $this->activityResponse($activity, true),
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to retrieve activity details', 'debug' => $e->getMessage()], 500);
        }
    }

    /**
     * Get My Activities
     *
     * Returns activities the authenticated user has created or joined.
     *
     * @authenticated
     * @group User Activities API
     *
     * @queryParam type     string  Filter by "created" or "joined". Example: created
     * @queryParam status   integer Participant status filter (10=confirmed, 20=cancelled). Example: 10
     * @queryParam per_page integer Records per page, default 15. Example: 15
     *
     */
    public function getMyUserActivities(Request $request)
    {
        try {
            $userId = auth()->user()->id;
            $type   = $request->input('type'); // 'created' | 'joined' | null (both)

            $with = [
                'court:id,venue_sport_id,name,image',
                'court.venueSport.venue:id,name,address_1,city,state,image',
                'court.venueSport.sport:id,name,slug,icon',
                'createdBy:id,email,profile_picture',
            ];

            if ($type === 'created') {
                // Activities I created
                $query = CourtCalendar::userActivities()
                    ->where('is_event', true)
                    ->where('created_by_user_id', $userId)
                    ->where('status', 10)
                    ->with($with)
                    ->withCount(['confirmedParticipants as participants_count']);

                $perPage    = $request->input('per_page', 15);
                $activities = $query->orderBy('date', 'desc')->paginate($perPage);
                $activities->getCollection()->transform(fn($a) => $this->activityResponse($a));

                return response()->json(['status' => 'success', 'data' => $activities]);
            }

            // Activities I joined (includes my created ones via participant record)
            $query = CourtCalendarParticipant::where('user_id', $userId)
                ->whereHas('courtCalendar', fn($q) => $q->userActivities()->where('is_event', true)->where('status', 10))
                ->with([
                    'courtCalendar' => fn($q) => $q->with($with)->withCount(['confirmedParticipants as participants_count']),
                ]);

            if ($request->filled('status')) {
                $query->where('status', (int) $request->status);
            }

            if ($type === 'joined') {
                // Exclude activities I created (show only ones I joined but didn't create)
                $query->whereHas('courtCalendar', fn($q) => $q->where('created_by_user_id', '!=', $userId));
            }

            $perPage        = $request->input('per_page', 15);
            $participations = $query->orderBy('joined_at', 'desc')->paginate($perPage);

            $participations->getCollection()->transform(function ($p) {
                $p->append('encrypted_id');
                $activity = $p->courtCalendar;
                return [
                    'registration_id'     => $p->encrypted_id,
                    'registration_status' => $p->status_label,
                    'joined_at'           => $p->joined_at,
                    'cancelled_at'        => $p->cancelled_at,
                    'cancellation_reason' => $p->cancellation_reason,
                    'activity'            => $activity ? $this->activityResponse($activity) : null,
                ];
            });

            return response()->json(['status' => 'success', 'data' => $participations]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to retrieve activities', 'debug' => $e->getMessage()], 500);
        }
    }

    /**
     * Cancel User Activity
     *
     * Allows the activity creator to cancel their activity.
     * All confirmed participants are automatically cancelled.
     *
     * @authenticated
     * @group User Activities API
     *
     * @bodyParam id                  string required Encrypted activity ID. Example: E2
     * @bodyParam cancellation_reason string optional Reason for cancellation. Example: Venue unavailable.
     *
     */
    public function cancelUserActivity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'                  => ['required'],
            'cancellation_reason' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $id       = Helper::decode($request->id);
            $activity = CourtCalendar::userActivities()
                ->where('id', $id)
                ->where('created_by_user_id', auth()->user()->id)
                ->where('status', 10)
                ->first();

            if (!$activity) {
                return response()->json(['status' => 'error', 'message' => 'Activity not found or you are not the organiser'], 404);
            }

            if ($activity->date < Carbon::today()) {
                return response()->json(['status' => 'error', 'message' => 'Cannot cancel a past activity'], 422);
            }

            DB::beginTransaction();

            // Cancel all participant registrations
            CourtCalendarParticipant::where('court_calendar_id', $id)
                ->where('status', CourtCalendarParticipant::STATUS_CONFIRMED)
                ->update([
                    'status'               => CourtCalendarParticipant::STATUS_CANCELLED,
                    'cancelled_at'         => Carbon::now(),
                    'cancellation_reason'  => $request->cancellation_reason ?? 'Activity cancelled by organiser',
                ]);

            // Mark the activity itself as suspended
            $activity->status       = 20;
            $activity->is_available = false;
            $activity->save();

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Activity cancelled successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Failed to cancel activity', 'debug' => $e->getMessage()], 500);
        }
    }

    // ── Private helper ────────────────────────────────────────────────────────

    private function activityResponse(CourtCalendar $activity, bool $detailed = false): array
    {
        $activity->append('encrypted_id');

        $participantsCount = $activity->participants_count
            ?? $activity->confirmedParticipants()->count();

        $availableSpots = is_null($activity->max_participants)
            ? null
            : max(0, $activity->max_participants - $participantsCount);

        $data = [
            'id'                    => $activity->encrypted_id,
            'event_title'           => $activity->event_title,
            'event_description'     => $activity->event_description,
            'date'                  => $activity->date->format('Y-m-d'),
            'day_of_week'           => $activity->day_of_week,
            'start_time'            => $activity->start_time,
            'end_time'              => $activity->end_time,
            'max_participants'      => $activity->max_participants,
            'participants_count'    => $participantsCount,
            'available_spots'       => $availableSpots,
            'is_full'               => !is_null($availableSpots) && $availableSpots === 0,
            'price_per_participant' => $activity->price_per_participant,
            'organiser' => $activity->createdBy ? [
                'id'    => Helper::encode($activity->createdBy->id),
                'email'  => $activity->createdBy->email,
                'photo' => $activity->createdBy->profile_picture_path_new ?? null,
            ] : null,
            'court' => $activity->court ? [
                'id'    => $activity->court->encrypted_id,
                'name'  => $activity->court->name,
                'image' => $activity->court->image_path,
            ] : null,
            'venue' => $activity->court?->venueSport?->venue ? [
                'id'      => $activity->court->venueSport->venue->encrypted_id,
                'name'    => $activity->court->venueSport->venue->name,
                'address' => $activity->court->venueSport->venue->address_1,
                'city'    => $activity->court->venueSport->venue->city,
                'state'   => $activity->court->venueSport->venue->state,
                'image'   => $activity->court->venueSport->venue->image_path,
            ] : null,
            'sport' => $activity->court?->venueSport?->sport ? [
                'id'   => $activity->court->venueSport->sport->encrypted_id,
                'name' => $activity->court->venueSport->sport->name,
                'icon' => $activity->court->venueSport->sport->icon_path,
            ] : null,
            'attendees' => $activity->confirmedParticipants->map(fn($p) => $p->user ? [
                'email'               => $p->user->email,
                'profile_picture_path' => $p->user->profile_picture_path_new,
            ] : null)->filter()->values(),
        ];

        if ($detailed && $activity->court?->venueSport?->venue) {
            $venue = $activity->court->venueSport->venue;
            $data['venue']['address_2'] = $venue->address_2 ?? null;
            $data['venue']['postcode']  = $venue->postcode ?? null;
            $data['venue']['gmap_link'] = $venue->gmap_link ?? null;
            $data['venue']['waze_link'] = $venue->waze_link ?? null;
        }

        return $data;
    }

    private function participantResponse(CourtCalendarParticipant $participant, CourtCalendar $event): array
    {
        return [
            'registration_id'       => $participant->encrypted_id,
            'event_id'              => $event->encrypted_id,
            'event_title'           => $event->event_title,
            'date'                  => $event->date->format('Y-m-d'),
            'start_time'            => $event->start_time,
            'end_time'              => $event->end_time,
            'price_per_participant' => $event->price_per_participant,
            'status'                => $participant->status_label,
            'joined_at'             => $participant->joined_at,
        ];
    }
}
