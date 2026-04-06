<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class Vehicle extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'driver_id',
        'company_id',
        'photo',
        'name',
        'license_plate',
        'trailer_number',
        'road_tax_number',
        'insurance_number',
        'permit_number',
        'road_tax_expiry_date',
        'insurance_expiry_date',
        'permit_start_date',
        'permit_expiry_date',
        'inspection_expiry_date',
        'in_service',
        'type',
        'tngsn',
        'insurance_start_date',
        'status',
    ];

    public function employee() {
        return $this->belongsTo( Employee::class, 'driver_id' );
    }
    
    public function company() {
        return $this->belongsTo( Company::class, 'company_id' );
    }

    public function getPathAttribute() {
        return $this->attributes['photo'] ? asset( 'storage/' . $this->attributes['photo'] ) : null;
    }

    public function getLocalPermitStartDateAttribute() {
        return $this->attributes['permit_start_date'] ?
        Carbon::createFromFormat( 'Y-m-d H:i:s', $this->attributes['permit_start_date'] )->setTimezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d' )
        : null;
    }

    public function getLocalInsuranceStartDateAttribute() {
        return $this->attributes['insurance_start_date'] ?
        Carbon::createFromFormat( 'Y-m-d', $this->attributes['insurance_start_date'] )->setTimezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d' )
        : null;
    }

    public function getLocalRoadTaxExpiryDateAttribute() {
        return $this->attributes['road_tax_expiry_date'] ?
        Carbon::createFromFormat( 'Y-m-d', $this->attributes['road_tax_expiry_date'] )->setTimezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d' )
        : null;
    }

    public function getLocalInsuranceExpiryDateAttribute() {
        return $this->attributes['insurance_expiry_date'] ?
        Carbon::createFromFormat( 'Y-m-d', $this->attributes['insurance_expiry_date'] )->setTimezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d' )
        : null;
    }

    public function getLocalPermitExpiryDateAttribute() {
        return $this->attributes['permit_expiry_date'] ?
        Carbon::createFromFormat( 'Y-m-d', $this->attributes['permit_expiry_date'] )->setTimezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d' )
        : null;
    }

    public function getLocalInspectionExpiryDateAttribute() {
        return $this->attributes['inspection_expiry_date'] ?
        Carbon::createFromFormat( 'Y-m-d H:i:s', $this->attributes['inspection_expiry_date'] )->setTimezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d' )
        : null;
    }

    public function getLocalRoadTaxExpiryDateStatusAttribute() {
        return $this->attributes['road_tax_expiry_date'] ?
            $this->attributes['road_tax_expiry_date'] < Carbon::now() ? true : false
        : false;
    }

    public function getLocalInsuranceExpiryDateStatusAttribute() {
        return $this->attributes['insurance_expiry_date'] ?
            $this->attributes['insurance_expiry_date'] < Carbon::now() ? true : false
        : false;
    }

    public function getLocalInspectionExpiryDateStatusAttribute() {
        return $this->attributes['inspection_expiry_date'] ?
            $this->attributes['inspection_expiry_date'] < Carbon::now() ? true : false
        : false;
    }

    public function getLocalPermitExpiryDateStatusAttribute() {
        return $this->attributes['permit_expiry_date'] ?
            $this->attributes['permit_expiry_date'] < Carbon::now() ? true : false
        : false;
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'driver_id',
        'company_id',
        'photo',
        'name',
        'license_plate',
        'trailer_number',
        'road_tax_number',
        'insurance_number',
        'permit_number',
        'road_tax_expiry_date',
        'insurance_expiry_date',
        'permit_start_date',
        'permit_expiry_date',
        'inspection_expiry_date',
        'in_service',
        'type',
        'tngsn',
        'insurance_start_date',
        'status',
    ];

    protected static $logName = 'vehicles';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} vehicle";
    }
}
