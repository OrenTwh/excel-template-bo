<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class TyreRecord extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'vehicle_id',
        'vendor_id',
        'part_id',
        'unit_price',
        'job_month',
        'job_no',
        'job_no_full',
        'purchase_bill_reference',
        'purchase_date',
    ];

    public function items() {
        return $this->hasMany( TyreRecordItem::class, 'tyre_record_id' );
    }

    public function vendor() {
        return $this->belongsTo( Vendor::class, 'vendor_id' );
    }

    public function part() {
        return $this->belongsTo( Part::class, 'part_id' );
    }

    public function vehicle() {
        return $this->belongsTo( Vehicle::class, 'vehicle_id' );
    }

    public function documents() {
        return $this->hasMany( Document::class, 'module_id' )->where( 'module', 'App\Models\TyreRecord' );
    }

    public function getLocalPurchaseDateAttribute() {
        return Carbon::createFromFormat( 'Y-m-d H:i:s', $this->attributes['purchase_date'] )->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d' );
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'vehicle_id',
        'job_month',
        'job_no',
        'job_no_full',
        'purchase_bill_reference',
        'purchase_date',
    ];

    protected static $logName = 'tyre_records';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} tyre record";
    }
}
