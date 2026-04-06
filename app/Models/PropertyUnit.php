<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class PropertyUnit extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'property_id',
        'unit_number',
        'unit_name',
        'floor',
        'bedrooms',
        'bathrooms',
        'balcony',
        'storeroom',
        'parking_spaces',
        'built_up_area',
        'selling_price',
        'rental_price',
        'maintenance_fee',
        'unit_status', // available, sold, rented, reserved
        'availability_date',
        'remarks',
        'status',
        'projects_floorplan_id',
        'is_dual_key_unit',
        'block_name',
        'unit_type',
        'rental_price_per_night',
        'available_timeslot',
        'number_of_guests',
        'available_days',
    ];

    protected $casts = [
        'availability_date' => 'date',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function getDisplayFloorAttribute()
    {
        $floor = $this->attributes['floor'];

        // If no property loaded, just return floor as string
        if ( ! $this->property ) {
            return (string) $floor;
        }

        $scheme = ( int ) $this->property->flooring_scheme;

        if ( $scheme === 1 && ( int ) $floor === 4 ) {
            return '3A';
        }

        return (string) $floor;
    }

    public function shortRentals()
    {
        return $this->hasMany(\App\Models\PropertyShortRental::class, 'property_unit_id');
    }

    /**
     * Get all prices in different currencies for this property unit
     */
    public function prices()
    {
        return $this->hasMany(\App\Models\PropertyUnitPrice::class, 'property_unit_id');
    }

    public function rentalReviews()
    {
        return $this->hasMany(RentalReview::class);
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'property_id',
        'unit_number',
        'unit_name',
        'floor',
        'bedrooms',
        'bathrooms',
        'balcony',
        'storeroom',
        'parking_spaces',
        'built_up_area',
        'selling_price',
        'rental_price',
        'maintenance_fee',
        'unit_status',
        'availability_date',
        'remarks',
        'status',
        'projects_floorplan_id',
        'is_dual_key_unit',
        'block_name',
        'unit_type',
        'rental_price_per_night',
        'available_timeslot',
        'available_days',
        'number_of_guests',
    ];

    protected static $logName = 'property_units';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} property unit";
    }

    /**
     * Get unit status label
     */
    public function getUnitStatusLabelAttribute()
    {
        switch ($this->unit_status) {
            case 'available':
                return 'Available';
            case 'sold':
                return 'Sold';
            case 'rented':
                return 'Rented';
            case 'reserved':
                return 'Reserved';
            default:
                return 'Unknown';
        }
    }

    public function getUnitTypeLabelAttribute()
    {
        $unitType = [
            1 => __('property_unit.for_sale_unit'),
            2 => __('property_unit.long_rental_unit'),
            3 => __('property_unit.short_rental_unit'),
            4 => __('property_unit.appointment_unit'),
        ];

        return $unitType[$this->attributes['unit_type']] ?? null;
    }

    public function getStatusLabelAttribute()
    {
        $statusLabels = [
            10 => __('datatables.activated'),
            20 => __('datatables.suspended'),
            21 => __('datatables.soft_deleted'),
            11 => __('datatables.sold'),
            12 => __( 'property_unit.booked_payment' ),
            13 => __( 'property_unit.rented' )
        ];

        return $statusLabels[$this->attributes['status']] ?? __('datatables.unknown');
    }
}