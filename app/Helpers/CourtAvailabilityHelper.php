<?php

namespace App\Helpers;

use App\Models\{Court, CourtBooking, CourtBookingGroup, CourtCalendar};
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * CourtAvailabilityHelper
 *
 * Single source of truth for court slot availability.
 * Merges two systems that were previously independent:
 *
 *   1. CourtCalendar  — admin-defined schedule blocks
 *      Blocked when: is_available = false  OR  is_event = true
 *
 *   2. CourtBooking   — user bookings
 *      Blocked when: status ∈ [PENDING_PAYMENT(1), UPCOMING(10)]
 *
 * Usage pattern mirrors the existing bulk-query approach already used in
 * getAvailableDates / getAvailableTimes so it drops in with minimal changes.
 */
class CourtAvailabilityHelper
{
    // ──────────────────────────────────────────────────────────────────────────
    // Single-slot check (used by isCourtAvailable / createBooking guard)
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Returns true when a court is free for the requested window.
     * Checks BOTH CourtCalendar blocks and existing CourtBooking conflicts.
     *
     * For courts with capacity > 1 (e.g. virtual/activity courts), checks whether
     * sum of overlapping participants + $requestedParticipants <= court.capacity.
     * For capacity = 1 courts the original exclusive-slot behaviour is preserved.
     *
     * @param int         $courtId
     * @param string      $date               Y-m-d
     * @param string      $startTime          H:i
     * @param string      $endTime            H:i
     * @param int|null    $excludeBookingId   Ignore this booking (useful on update)
     * @param int         $requestedParticipants  Number of participants being booked
     */
    public static function isCourtAvailable(
        int    $courtId,
        string $date,
        string $startTime,
        string $endTime,
        ?int   $excludeBookingId      = null,
        int    $requestedParticipants = 1
    ): bool {
        // 1. Calendar block check
        if (self::isBlockedByCalendar($courtId, $date, $startTime, $endTime)) {
            return false;
        }

        // 2. Capacity-aware booking conflict check
        $court    = Court::find($courtId);
        $capacity = $court ? (int) $court->capacity : 1;

        $query = CourtBooking::where('court_bookings.court_id', $courtId)
            ->where('court_bookings.booking_date', $date)
            ->whereHas('group', function ($q) {
                $q->whereIn('status', [
                    CourtBookingGroup::STATUS_PENDING_PAYMENT,
                    CourtBookingGroup::STATUS_UPCOMING,
                ]);
            })
            ->where('court_bookings.start_time', '<', $endTime)
            ->where('court_bookings.end_time',   '>', $startTime);

        if ($excludeBookingId) {
            $query->where('court_bookings.id', '!=', $excludeBookingId);
        }

        if ($capacity <= 1) {
            // Exclusive slot: any overlap blocks the court
            return !$query->exists();
        }

        // Shared capacity: check whether remaining slots can fit the request
        $bookedParticipants = (int) $query->sum('participants');
        return ($bookedParticipants + $requestedParticipants) <= $capacity;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Single-court calendar check (lightweight, no bulk fetch)
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Returns true if any CourtCalendar entry blocks the given window.
     * Matches specific date entries AND recurring entries by day_of_week.
     */
    public static function isBlockedByCalendar(
        int    $courtId,
        string $date,
        string $startTime,
        string $endTime
    ): bool {
        $dayOfWeek = strtolower(Carbon::parse($date)->format('l'));

        return CourtCalendar::where('court_id', $courtId)
            ->where('status', 10)
            ->where(function ($q) use ($date, $dayOfWeek) {
                $q->where('date', $date)
                  ->orWhere(function ($q2) use ($dayOfWeek) {
                      $q2->where('is_recurring', true)
                         ->where('day_of_week', $dayOfWeek);
                  });
            })
            ->where(function ($q) {
                // Blocked = admin marked unavailable OR the slot is reserved for an event
                $q->where('is_available', false)
                  ->orWhere('is_event', true);
            })
            // Overlap: calendar_start < endTime AND calendar_end > startTime
            ->where('start_time', '<', $endTime)
            ->where('end_time',   '>', $startTime)
            ->exists();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Bulk helpers (used by getAvailableDates / getAvailableTimes)
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Fetch all CourtCalendar blocks for a set of courts over a date range.
     *
     * Returns a nested Collection indexed by  [date][court_id] → Collection of blocks.
     * Each block has: court_id, start_time (H:i), end_time (H:i), reason.
     *
     * Recurring entries are expanded across the date range so callers
     * don't need to think about day_of_week logic.
     *
     * @param  array  $courtIds   plain integer IDs
     * @param  string $dateFrom   Y-m-d
     * @param  string $dateTo     Y-m-d
     * @return Collection         [dateStr => Collection[ court_id => Collection[block] ]]
     */
    public static function getCalendarBlocksBulk(
        array  $courtIds,
        string $dateFrom,
        string $dateTo
    ): Collection {
        // Fetch all relevant calendar entries once
        $entries = CourtCalendar::whereIn('court_id', $courtIds)
            ->where('status', 10)
            ->where(function ($q) {
                $q->where('is_available', false)
                  ->orWhere('is_event', true);
            })
            ->where(function ($q) use ($dateFrom, $dateTo) {
                // Specific dates in range  OR  recurring (any day_of_week)
                $q->whereBetween('date', [$dateFrom, $dateTo])
                  ->orWhere('is_recurring', true);
            })
            ->select('court_id', 'date', 'day_of_week', 'start_time', 'end_time', 'is_recurring', 'is_event', 'is_available', 'unavailability_reason')
            ->get()
            ->map(function ($e) {
                $e->start_time = Carbon::parse($e->start_time)->format('H:i');
                $e->end_time   = Carbon::parse($e->end_time)->format('H:i');
                return $e;
            });

        // Expand recurring entries across every day in the range
        $result  = collect();
        $current = Carbon::parse($dateFrom);
        $end     = Carbon::parse($dateTo);

        while ($current->lte($end)) {
            $dateStr   = $current->format('Y-m-d');
            $dayOfWeek = strtolower($current->format('l'));

            $dayBlocks = $entries->filter(function ($e) use ($dateStr, $dayOfWeek) {
                if (!$e->is_recurring) {
                    return Carbon::parse($e->date)->format('Y-m-d') === $dateStr;
                }
                return $e->day_of_week === $dayOfWeek;
            });

            if ($dayBlocks->isNotEmpty()) {
                $result[$dateStr] = $dayBlocks->groupBy('court_id');
            }

            $current->addDay();
        }

        return $result;
    }

    /**
     * Fetch all CourtCalendar blocks for a set of courts on a single date.
     * Returns Collection indexed by court_id → Collection of blocks.
     */
    public static function getCalendarBlocksForDate(
        array  $courtIds,
        string $date
    ): Collection {
        $bulk = self::getCalendarBlocksBulk($courtIds, $date, $date);
        return $bulk->get($date, collect());
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Slot-level conflict helper (called inside loops)
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Given pre-fetched booking rows and calendar block rows for a specific court,
     * returns true if the [slotStart, slotEnd) window is blocked by either source.
     *
     * For capacity > 1 courts, "blocked" means booked participants >= capacity.
     *
     * @param Collection $courtBookings        CourtBooking rows for this court (with H:i times)
     * @param Collection $courtCalBlocks       CourtCalendar block rows for this court (with H:i times)
     * @param string     $slotStart            H:i
     * @param string     $slotEnd              H:i
     * @param int        $capacity             Court capacity (1 = exclusive)
     * @param int        $requestedParticipants Participants being requested
     */
    public static function isSlotConflicted(
        Collection $courtBookings,
        Collection $courtCalBlocks,
        string     $slotStart,
        string     $slotEnd,
        int        $capacity             = 1,
        int        $requestedParticipants = 1
    ): bool {
        $calendarConflict = $courtCalBlocks->contains(
            fn($c) => $c->start_time < $slotEnd && $c->end_time > $slotStart
        );

        if ($calendarConflict) {
            return true;
        }

        $overlapping = $courtBookings->filter(
            fn($b) => $b->start_time < $slotEnd && $b->end_time > $slotStart
        );

        if ($capacity <= 1) {
            return $overlapping->isNotEmpty();
        }

        $bookedParticipants = (int) $overlapping->sum('participants');
        return ($bookedParticipants + $requestedParticipants) > $capacity;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Convenience: get available court IDs for one slot (used by getAvailableTimes)
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Returns an array of encrypted court IDs that are free for the given slot.
     *
     * @param Collection $courts               Court model collection
     * @param Collection $bookingsByCourt      Grouped booking rows (court_id => Collection)
     * @param Collection $calBlocksByCourt     Grouped calendar block rows (court_id => Collection)
     * @param string     $slotStart            H:i
     * @param string     $slotEnd              H:i
     * @param int        $requestedParticipants Participants being requested
     * @return array  encrypted_id[]
     */
    public static function getAvailableCourtIds(
        Collection $courts,
        Collection $bookingsByCourt,
        Collection $calBlocksByCourt,
        string     $slotStart,
        string     $slotEnd,
        int        $requestedParticipants = 1
    ): array {
        $available = [];

        foreach ($courts as $court) {
            $courtBookings  = $bookingsByCourt->get($court->id, collect());
            $courtCalBlocks = $calBlocksByCourt->get($court->id, collect());
            $capacity       = isset($court->capacity) ? (int) $court->capacity : 1;

            if (!self::isSlotConflicted($courtBookings, $courtCalBlocks, $slotStart, $slotEnd, $capacity, $requestedParticipants)) {
                $available[] = $court->encrypted_id;
            }
        }

        return $available;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Describe why a slot is unavailable (useful for API responses / debugging)
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Returns a human-readable reason string for why a slot is blocked,
     * or null if the slot is available.
     */
    public static function getUnavailabilityReason(
        int    $courtId,
        string $date,
        string $startTime,
        string $endTime
    ): ?string {
        $dayOfWeek = strtolower(Carbon::parse($date)->format('l'));

        $calBlock = CourtCalendar::where('court_id', $courtId)
            ->where('status', 10)
            ->where(function ($q) use ($date, $dayOfWeek) {
                $q->where('date', $date)
                  ->orWhere(function ($q2) use ($dayOfWeek) {
                      $q2->where('is_recurring', true)
                         ->where('day_of_week', $dayOfWeek);
                  });
            })
            ->where(function ($q) {
                $q->where('is_available', false)
                  ->orWhere('is_event', true);
            })
            ->where('start_time', '<', $endTime)
            ->where('end_time',   '>', $startTime)
            ->first();

        if ($calBlock) {
            if ($calBlock->is_event) {
                return 'Reserved for event: ' . ($calBlock->event_title ?? 'event');
            }
            return $calBlock->unavailability_reason ?? 'Marked unavailable';
        }

        $court    = Court::find($courtId);
        $capacity = $court ? (int) $court->capacity : 1;

        $overlappingQuery = CourtBooking::where('court_bookings.court_id', $courtId)
            ->where('court_bookings.booking_date', $date)
            ->whereHas('group', function ($q) {
                $q->whereIn('status', [
                    CourtBookingGroup::STATUS_PENDING_PAYMENT,
                    CourtBookingGroup::STATUS_UPCOMING,
                ]);
            })
            ->where('court_bookings.start_time', '<', $endTime)
            ->where('court_bookings.end_time',   '>', $startTime);

        if ($capacity <= 1) {
            return $overlappingQuery->exists() ? 'Already booked' : null;
        }

        $bookedParticipants = (int) $overlappingQuery->sum('participants');
        if ($bookedParticipants >= $capacity) {
            return "Fully booked ({$bookedParticipants}/{$capacity} participants)";
        }

        return null;
    }
}
