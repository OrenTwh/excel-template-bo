<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class Booking extends Model
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
        'completed_date',
        'status',
        'fullname',
        'email',
        'calling_code',
        'phone_number',
        'preferred_time',
        'bedrooms',
        'type',
        'projects_floorplan_id',
        'booking_type',
    ];

    // protected function getPreferredTimeAttribute()
    // {
    //     return $this->attributes['preferred_time'] ? Carbon::createFromFormat( 'H:i:s', $this->attributes['preferred_time'] )->format( 'H:i' ) : null;
    // }

    public static function preferredTimeOptions(): array {
        $times = [];
        for ( $hour = 9; $hour <= 18; $hour++ ) {
            $value = str_pad( $hour, 2, '0', STR_PAD_LEFT ) . ':00';
            $label = Carbon::createFromTime( $hour, 0 )
                ->format( 'h:i A' ); // 12-hour format with AM/PM
            $times[$value] = $label;
        }
        return $times;
    }

    public function property() {
        return $this->belongsTo( Property::class, 'property_id' );
    }

    public function user() {
        return $this->belongsTo( User::class, 'user_id' );
    }

    public function agent() {
        return $this->belongsTo( Agent::class, 'agent_id' );
    }

    public function propertyUnit() {
        return $this->belongsTo( PropertyUnit::class, 'property_unit_id' );
    }

    public function projectFloorplan() {
        return $this->belongsTo( ProjectFloorplan::class, 'projects_floorplan_id' );
    }

    public function bookingProperties() {
        return $this->hasMany( BookingProperty::class, 'booking_id' );
    }

    public function tenancyAgreements() {
        return $this->hasMany( TenancyAgreement::class, 'booking_id' );
    }

    public function tenancyAgreement() {
        return $this->hasOne( TenancyAgreement::class, 'booking_id' )->latest();
    }

    public function getBookingDateAttribute() {
        return ! empty($this->attributes['booking_date'])
        ? Carbon::parse($this->attributes['booking_date'])
            ->timezone('Asia/Kuala_Lumpur')
            ->format('Y-m-d')
        : null;
    }

    public function getCompletedDateAttribute() {
        return ! empty($this->attributes['completed_date'])
        ? Carbon::parse($this->attributes['completed_date'])
            ->timezone('Asia/Kuala_Lumpur')
            ->format('Y-m-d')
        : null;
    }

    public function getBookingTypeLabelAttribute(){
        
        $furnishingType = [
            1  => __( 'booking.buy'),
            2  => __( 'booking.long_rent' ),
            3  => __( 'booking.short_rent' ),
        ];

        return $furnishingType[$this->attributes['booking_type']] ?? '';
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'property_id',
        'property_unit_id',
        'user_id',
        'agent_id',
        'custom_messages',
        'remarks',
        'booking_date',
        'completed_date',
        'status',
        'fullname',
        'email',
        'calling_code',
        'phone_number',
        'preferred_time',
        'bedrooms',
        'type',
        'projects_floorplan_id',
        'booking_type',
    ];

    protected static $logName = 'bookings';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} booking";
    }
}
