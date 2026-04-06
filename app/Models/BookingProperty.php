<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class BookingProperty extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'booking_id',
        'property_id',
        'property_unit_id',
        'status',
    ];

    /**
     * Relationships
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    public function propertyUnit()
    {
        return $this->belongsTo(PropertyUnit::class, 'property_unit_id');
    }

    /**
     * Activity Log Configuration
     */
    protected static $logAttributes = [
        'booking_id',
        'property_id',
        'property_unit_id',
        'status',
    ];

    protected static $logName = 'booking_properties';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "{$eventName} booking property";
    }
}