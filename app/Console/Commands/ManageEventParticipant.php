<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\{CourtCalendar, CourtCalendarParticipant, User};
use Carbon\Carbon;

class ManageEventParticipant extends Command
{
    protected $signature = 'event:manage
                            {action         : Action to perform: join or leave}
                            {--user=        : User ID or email}
                            {--event=       : Event (CourtCalendar) ID}
                            {--reason=      : Cancellation reason (leave only)}';

    protected $description = 'Join or leave a court calendar event on behalf of a user';

    public function handle()
    {
        $action = strtolower($this->argument('action'));

        if (!in_array($action, ['join', 'leave'])) {
            $this->error('Action must be "join" or "leave".');
            return 1;
        }

        // ── Resolve user ─────────────────────────────────────────────────────
        $userInput = $this->option('user');
        if (!$userInput) {
            $userInput = $this->ask('User ID or email');
        }

        $user = is_numeric($userInput)
            ? User::find($userInput)
            : User::where('email', $userInput)->first();

        if (!$user) {
            $this->error("User not found: {$userInput}");
            return 1;
        }

        $this->line("User : [{$user->id}] {$user->fullname} ({$user->email})");

        // ── Resolve event ────────────────────────────────────────────────────
        $eventInput = $this->option('event');
        if (!$eventInput) {
            $eventInput = $this->ask('Event ID');
        }

        $event = CourtCalendar::where('is_event', true)
            ->where('status', 10)
            ->find((int) $eventInput);

        if (!$event) {
            $this->error("Event not found or not active: {$eventInput}");
            return 1;
        }

        $this->line("Event: [{$event->id}] {$event->event_title} on {$event->date->format('Y-m-d')} {$event->start_time}–{$event->end_time}");

        // ── Perform action ───────────────────────────────────────────────────
        return $action === 'join'
            ? $this->joinEvent($user, $event)
            : $this->leaveEvent($user, $event);
    }

    private function joinEvent(User $user, CourtCalendar $event): int
    {
        if (!$event->is_available) {
            $this->error('This event is not available.');
            return 1;
        }

        if ($event->date < Carbon::today()) {
            $this->error('Cannot join a past event.');
            return 1;
        }

        $existing = CourtCalendarParticipant::where('court_calendar_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            if ($existing->status === CourtCalendarParticipant::STATUS_CONFIRMED) {
                $this->warn('User has already joined this event.');
                return 0;
            }

            // Re-join after cancel
            if (!$this->confirm('User previously left this event. Re-join?', true)) {
                $this->line('Aborted.');
                return 0;
            }

            DB::beginTransaction();
            $existing->status              = CourtCalendarParticipant::STATUS_CONFIRMED;
            $existing->joined_at           = Carbon::now();
            $existing->cancelled_at        = null;
            $existing->cancellation_reason = null;
            $existing->save();
            DB::commit();

            $this->info("✔ {$user->fullname} re-joined the event.");
            return 0;
        }

        // Check capacity
        $participantsCount = $event->confirmedParticipants()->count();
        if (!is_null($event->max_participants) && $participantsCount >= $event->max_participants) {
            $this->error("Event is full ({$participantsCount}/{$event->max_participants}).");
            return 1;
        }

        DB::beginTransaction();
        CourtCalendarParticipant::create([
            'court_calendar_id' => $event->id,
            'user_id'           => $user->id,
            'status'            => CourtCalendarParticipant::STATUS_CONFIRMED,
            'joined_at'         => Carbon::now(),
        ]);
        DB::commit();

        $this->info("✔ {$user->fullname} joined the event.");
        return 0;
    }

    private function leaveEvent(User $user, CourtCalendar $event): int
    {
        if ($event->date < Carbon::today()) {
            $this->error('Cannot leave a past event.');
            return 1;
        }

        $participant = CourtCalendarParticipant::where('court_calendar_id', $event->id)
            ->where('user_id', $user->id)
            ->where('status', CourtCalendarParticipant::STATUS_CONFIRMED)
            ->first();

        if (!$participant) {
            $this->error('User is not registered (confirmed) for this event.');
            return 1;
        }

        $reason = $this->option('reason') ?? $this->ask('Cancellation reason (optional, press Enter to skip)');

        if (!$this->confirm("Remove {$user->fullname} from this event?", true)) {
            $this->line('Aborted.');
            return 0;
        }

        DB::beginTransaction();
        $participant->status              = CourtCalendarParticipant::STATUS_CANCELLED;
        $participant->cancelled_at        = Carbon::now();
        $participant->cancellation_reason = $reason ?: null;
        $participant->save();
        DB::commit();

        $this->info("✔ {$user->fullname} has left the event.");
        return 0;
    }
}
