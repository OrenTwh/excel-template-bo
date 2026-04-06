<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Helper;
use Carbon\Carbon;

class CourtBooking extends Model
{
    use HasFactory, LogsActivity;

    // ── Status aliases (authoritative values live on CourtBookingGroup) ───────
    const STATUS_PENDING_PAYMENT = CourtBookingGroup::STATUS_PENDING_PAYMENT;
    const STATUS_UPCOMING        = CourtBookingGroup::STATUS_UPCOMING;
    const STATUS_COMPLETE        = CourtBookingGroup::STATUS_COMPLETE;
    const STATUS_SUSPENDED       = CourtBookingGroup::STATUS_SUSPENDED;
    const STATUS_CANCELED        = CourtBookingGroup::STATUS_CANCELED;

    public static function statusLabels(): array
    {
        return CourtBookingGroup::statusLabels();
    }

    protected $fillable = [
        'court_booking_group_id',
        'court_id',
        'booking_date',
        'start_time',
        'end_time',
        'duration_hours',
        'participants',
        'price',
        'total_amount',
        'discount_amount',
        'notes',
    ];

    protected $casts = [
        'court_booking_group_id' => 'integer',
        'court_id'               => 'integer',
        'booking_date'           => 'date',
        'duration_hours'         => 'integer',
        'participants'           => 'integer',
        'price'                  => 'decimal:2',
        'total_amount'           => 'decimal:2',
        'discount_amount'        => 'decimal:2',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function group()
    {
        return $this->belongsTo(CourtBookingGroup::class, 'court_booking_group_id');
    }

    public function court()
    {
        return $this->belongsTo(Court::class);
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getEncryptedIdAttribute()
    {
        return Helper::encode($this->attributes['id']);
    }

    // ── Activity log ──────────────────────────────────────────────────────────

    protected static $logAttributes = [
        'court_booking_group_id', 'court_id', 'booking_date',
        'start_time', 'end_time', 'duration_hours', 'participants',
        'price', 'total_amount', 'discount_amount', 'notes',
    ];

    protected static $logName      = 'CourtBooking';
    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "{$eventName} court booking";
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->timezone('Asia/Kuala_Lumpur')->format('Y-m-d H:i:s');
    }
}
