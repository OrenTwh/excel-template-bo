<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Helper;
use Carbon\Carbon;

class CourtBookingGroup extends Model
{
    use HasFactory, LogsActivity;

    // ── Status codes ──────────────────────────────────────────────────────────
    const STATUS_PENDING_PAYMENT = 1;
    const STATUS_UPCOMING        = 10;
    const STATUS_COMPLETE        = 11;
    const STATUS_SUSPENDED       = 20;
    const STATUS_CANCELED        = 21;

    public static function statusLabels(): array
    {
        return [
            self::STATUS_PENDING_PAYMENT => 'Pending Payment',
            self::STATUS_UPCOMING        => 'Upcoming',
            self::STATUS_COMPLETE        => 'Complete',
            self::STATUS_SUSPENDED       => 'Suspended',
            self::STATUS_CANCELED        => 'Canceled',
        ];
    }

    protected $fillable = [
        'user_id',
        'group_no',
        'voucher_id',
        'subtotal',
        'discount_amount',
        'total_amount',
        'payment_status',
        'payment_gateway',
        'payment_gateway_ref',
        'payment_attempt',
        'status',
        'confirmed_at',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'user_id'         => 'integer',
        'voucher_id'      => 'integer',
        'subtotal'        => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount'    => 'decimal:2',
        'payment_attempt' => 'integer',
        'confirmed_at'    => 'datetime',
        'cancelled_at'    => 'datetime',
        'status'          => 'integer',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function courtBookings()
    {
        return $this->hasMany(CourtBooking::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getEncryptedIdAttribute()
    {
        return Helper::encode($this->attributes['id']);
    }

    /** Alias so existing code referencing ->booking_no still works */
    public function getBookingNoAttribute(): string
    {
        return $this->attributes['group_no'];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabels()[$this->attributes['status']] ?? 'Unknown';
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopePendingPayment($query)
    {
        return $query->where('status', self::STATUS_PENDING_PAYMENT);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', self::STATUS_UPCOMING);
    }

    public function scopeComplete($query)
    {
        return $query->where('status', self::STATUS_COMPLETE);
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', self::STATUS_SUSPENDED);
    }

    public function scopeCanceled($query)
    {
        return $query->where('status', self::STATUS_CANCELED);
    }

    // ── Activity log ──────────────────────────────────────────────────────────

    protected static $logAttributes = [
        'user_id', 'group_no', 'voucher_id', 'subtotal', 'discount_amount', 'total_amount',
        'payment_status', 'payment_gateway', 'payment_gateway_ref', 'payment_attempt',
        'status', 'confirmed_at', 'cancelled_at', 'cancellation_reason',
    ];

    protected static $logName    = 'CourtBookingGroup';
    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "{$eventName} court booking group";
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->timezone('Asia/Kuala_Lumpur')->format('Y-m-d H:i:s');
    }
}
