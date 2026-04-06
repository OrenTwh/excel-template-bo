<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;
use App\Models\User;

class CourtCalendar extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'court_id',
        'date',
        'day_of_week',
        'start_time',
        'end_time',
        'is_available',
        'is_recurring',
        'unavailability_reason',
        'special_price',
        'is_event',
        'event_title',
        'event_description',
        'max_participants',
        'price_per_participant',
        'external_form_link',
        'status',
        'created_by_user_id',
    ];

    protected $casts = [
        'court_id'             => 'integer',
        'date'                 => 'date',
        'is_available'         => 'boolean',
        'is_recurring'         => 'boolean',
        'special_price'        => 'decimal:2',
        'is_event'             => 'boolean',
        'max_participants'     => 'integer',
        'price_per_participant'=> 'decimal:2',
        'status'               => 'integer',
        'created_by_user_id'   => 'integer',
    ];

    public function court()
    {
        return $this->belongsTo(Court::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    // Scope: user-created activities only
    public function scopeUserActivities($query)
    {
        return $query->whereNotNull('created_by_user_id');
    }

    // Scope: admin-created events only
    public function scopeAdminEvents($query)
    {
        return $query->whereNull('created_by_user_id');
    }

    public function participants()
    {
        return $this->hasMany(CourtCalendarParticipant::class);
    }

    public function confirmedParticipants()
    {
        return $this->hasMany(CourtCalendarParticipant::class)
            ->where('status', CourtCalendarParticipant::STATUS_CONFIRMED);
    }

    public function getIsFullAttribute(): bool
    {
        if (!$this->is_event || is_null($this->max_participants)) {
            return false;
        }

        return $this->confirmedParticipants()->count() >= $this->max_participants;
    }

    public function getAvailableSpotsAttribute(): ?int
    {
        if (!$this->is_event || is_null($this->max_participants)) {
            return null;
        }

        return max(0, $this->max_participants - $this->confirmedParticipants()->count());
    }

    public function getEncryptedIdAttribute()
    {
        return Helper::encode($this->attributes['id']);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeUnavailable($query)
    {
        return $query->where('is_available', false);
    }

    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('date', $date);
    }

    public function scopeForDayOfWeek($query, $dayOfWeek)
    {
        return $query->where('day_of_week', $dayOfWeek);
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->timezone('Asia/Kuala_Lumpur')->format('Y-m-d H:i:s');
    }

    protected static $logAttributes = [
        'court_id',
        'date',
        'day_of_week',
        'start_time',
        'end_time',
        'is_available',
        'is_recurring',
        'unavailability_reason',
        'special_price',
        'status',
    ];

    protected static $logName = 'CourtCalendar';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "{$eventName} ";
    }
}
