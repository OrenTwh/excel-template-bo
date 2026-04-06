<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Helper;

class PropertyRental extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'property_id',
        'property_unit_id',
        'user_id',
        'agent_id',
        'custom_messages',
        'remarks',
        'booking_date',
        'checkout_date',
        'start_date',
        'end_date',
        'rental_duration',
        'fullname',
        'email',
        'calling_code',
        'phone_number',
        'status',
        'identification_number',
        'rating',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'checkout_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'rating' => 'decimal:2',
    ];

    protected static $logAttributes = [
        'property_id',
        'property_unit_id',
        'user_id',
        'agent_id',
        'custom_messages',
        'remarks',
        'booking_date',
        'checkout_date',
        'start_date',
        'end_date',
        'rental_duration',
        'fullname',
        'email',
        'calling_code',
        'phone_number',
        'status',
        'rating',
        'identification_number',
    ];

    protected static $logName = 'property_rentals';
    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "{$eventName} property rental";
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->timezone('Asia/Kuala_Lumpur')->format('Y-m-d H:i:s');
    }

    /**
     * Relationships
     */
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

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function agreement()
    {
        return $this->hasOne(TenancyAgreement::class);
    }

    public function rentalReviews()
    {
        return $this->hasMany(RentalReview::class, 'property_rental_id');
    }

    public function rentalReview()
    {
        return $this->hasOne(RentalReview::class, 'property_rental_id')->latest();
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
            1 => __('datatables.pending'),
            10 => __('datatables.activated'),
            11 => __('datatables.completed'),
            20 => __('datatables.suspended'),
            21 => __('datatables.cancelled'),
        ];

        return $statusLabels[$this->attributes['status']] ?? __('datatables.unknown');
    }

    /**
     * Static methods
     */
    public static function statusOptions()
    {
        return [
            1 => __('datatables.pending'),
            10 => __('datatables.activated'),
            11 => __('datatables.completed'),
            20 => __('datatables.suspended'),
            21 => __('datatables.cancelled'),
        ];
    }
}
