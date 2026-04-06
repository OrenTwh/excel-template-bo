<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class ServiceRecord extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'vehicle_id',
        'company_id',
        'reference',
        'document_reference',
        'workshop',
        'remarks',
        'meter_reading',
        'service_date',
    ];

    public function items() {
        return $this->hasMany( ServiceRecordItem::class, 'service_record_id' );
    }

    public function vehicle() {
        return $this->belongsTo( Vehicle::class, 'vehicle_id' );
    }

    public function company() {
        return $this->belongsTo( Company::class, 'company_id' );
    }

    public function documents() {
        return $this->hasMany( Document::class, 'module_id' )->where( 'module', 'App\Models\ServiceRecord' );
    }

    public function getDisplayMeterReadingAttribute() {
        return Helper::numberFormatV2( $this->attributes['meter_reading'], 2, true );
    }

    public function getLocalServiceDateAttribute() {
        return Carbon::createFromFormat( 'Y-m-d H:i:s', $this->attributes['service_date'] )->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d' );
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'vehicle_id',
        'company_id',
        'reference',
        'document_reference',
        'workshop',
        'remarks',
        'meter_reading',
        'service_date',
    ];

    protected static $logName = 'service_records';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} service record";
    }
}
