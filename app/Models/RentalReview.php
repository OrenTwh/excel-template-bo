<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class RentalReview extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'property_rental_id',
        'property_short_rental_id',
        'property_id',
        'property_unit_id',
        'user_id',
        'rating',
        'review_title',
        'review_text',
        'images',
        'response',
        'responded_by',
        'responded_at',
        'is_verified',
        'verified_at',
        'helpful_count',
        'report_count',
        'status',
        'reviewed_by',
        'reviewed_at',
        'moderation_notes',
        'stay_date',
        'stay_duration',
    ];

    protected $casts = [
        'images' => 'array',
        'is_verified' => 'boolean',
        'responded_at' => 'datetime',
        'verified_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'stay_date' => 'date',
        'helpful_count' => 'integer',
        'report_count' => 'integer',
        'stay_duration' => 'integer',
        'rating' => 'integer'
    ];

    /**
     * Relationships
     */
    public function propertyRental()
    {
        return $this->belongsTo(PropertyRental::class);
    }

    public function propertyShortRental()
    {
        return $this->belongsTo(PropertyShortRental::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function propertyUnit()
    {
        return $this->belongsTo(PropertyUnit::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function responder()
    {
        return $this->belongsTo(Administrator::class, 'responded_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(Administrator::class, 'reviewed_by');
    }

    /**
     * Accessors
     */
    public function getEncryptedIdAttribute()
    {
        return Helper::encode($this->attributes['id']);
    }

    public function getStatusLabelAttribute()
    {
        $statusLabels = [
            1 => 'Pending',
            10 => 'Approved',
            20 => 'Rejected',
            30 => 'Hidden',
        ];

        return $statusLabels[$this->attributes['status']] ?? 'Unknown';
    }

    /**
     * Scopes
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 10);
    }

    public function scopePending($query)
    {
        return $query->where('status', 1);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeForProperty($query, $propertyId)
    {
        return $query->where('property_id', $propertyId);
    }

    /**
     * Activity Log
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->timezone('Asia/Kuala_Lumpur')->format('Y-m-d H:i:s');
    }

    protected static $logAttributes = [
        'property_rental_id',
        'property_short_rental_id',
        'property_id',
        'property_unit_id',
        'user_id',
        'rating',
        'response',
        'status',
        'is_verified',
    ];

    protected static $logName = 'rental_reviews';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "{$eventName} rental review";
    }

    /**
     * Static Methods
     */
    public static function statusOptions()
    {
        return [
            1 => 'Pending',
            10 => 'Approved',
            20 => 'Rejected',
            30 => 'Hidden',
        ];
    }
}
