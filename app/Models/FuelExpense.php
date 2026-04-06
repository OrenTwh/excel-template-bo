<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class FuelExpense extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'company_id',
        'vehicle_id',
        'location',
        'day',
        'month',
        'year',
        'amount',
        'station',
        'status',
        'transaction_time',
    ];

    public function company() {
        return $this->belongsTo( Company::class, 'company_id' );
    }

    public function vehicle() {
        return $this->belongsTo( Vehicle::class, 'vehicle_id' );
    }

    public function getLocalTransactionTimeAttribute() {
        return Carbon::createFromFormat( 'Y-m-d H:i:s', $this->attributes['transaction_time'] )->setTimezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    public function getDisplayAmountAttribute() {
        return Helper::numberFormat( $this->attributes['amount'], 2 );
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'company_id',
        'vehicle_id',
        'location',
        'day',
        'month',
        'year',
        'amount',
        'station',
        'status',
        'transaction_time',
    ];

    protected static $logName = 'fuel_expenses';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} fuel expense";
    }
}
