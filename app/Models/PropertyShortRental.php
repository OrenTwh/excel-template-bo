<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Helper;

class PropertyShortRental extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'property_id',
        'property_unit_id',
        'user_id',
        'custom_messages',
        'remarks',
        'check_in_date',
        'check_in_time',
        'check_out_date',
        'check_out_time',
        'nights',
        'total_amount',
        'num_of_adult',
        'num_of_children',
        'fullname',
        'email',
        'calling_code',
        'phone_number',
        'status',
        'rating',
        'reviews',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    protected static $logAttributes = [
        'property_id',
        'property_unit_id',
        'user_id',
        'custom_messages',
        'remarks',
        'check_in_date',
        'check_in_time',
        'check_out_date',
        'check_out_time',
        'nights',
        'total_amount',
        'num_of_adult',
        'num_of_children',
        'fullname',
        'email',
        'calling_code',
        'phone_number',
        'status',
        'rating',
        'reviews',
    ];

    protected static $logName = 'property_short_rentals';
    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "{$eventName} property short rental";
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

    public function rentalReviews()
    {
        return $this->hasMany(RentalReview::class, 'property_short_rental_id');
    }

    public function rentalReview()
    {
        return $this->hasOne(RentalReview::class, 'property_short_rental_id')->latest();
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

    public function getFormattedTotalAmountAttribute()
    {
        return 'RM ' . number_format($this->total_amount, 2);
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
