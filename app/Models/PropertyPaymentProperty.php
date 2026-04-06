<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PropertyPaymentProperty extends Model
{
    use HasFactory, LogsActivity;

    protected $table = "property_payments_properties";

    protected $fillable = [
        'property_payment_id',
        'property_id',
        'property_unit_id',
        'status',
    ];

    /**
     * Relationships
     */
    public function propertyPayment()
    {
        return $this->belongsTo(PropertyPayment::class, 'property_payment_id');
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
        'property_payment_id',
        'property_id',
        'property_unit_id',
        'status',
    ];

    protected static $logName = 'property_payments_properties';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "{$eventName} property payments property";
    }
}