<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class TollExpense extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'vehicle_id',
        'transaction_number',
        'entry_location',
        'entry_sp',
        'exit_location',
        'exit_sp',
        'reload_location',
        'tag_number',
        'remarks',
        'day',
        'month',
        'year',
        'posted_date',
        'amount',
        'balance',
        'class',
        'type',
        'status',
        'transaction_time',
    ];

    public function vehicle() {
        return $this->belongsTo( Vehicle::class, 'vehicle_id' );
    }

    public function getTransactionTimeAttribute() {
        return Carbon::createFromFormat( 'Y-m-d H:i:s', $this->attributes['transaction_time'] )->setTimezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    public function getPostedDateAttribute() {
        return Carbon::createFromFormat( 'Y-m-d', $this->attributes['posted_date'] )->setTimezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d' );
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
        'vehicle_id',
        'transaction_number',
        'entry_location',
        'entry_sp',
        'exit_location',
        'exit_sp',
        'reload_location',
        'tag_number',
        'remarks',
        'day',
        'month',
        'year',
        'posted_date',
        'amount',
        'balance',
        'class',
        'type',
        'status',
        'transaction_time',
    ];

    protected static $logName = 'toll_expenses';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} toll expense";
    }
}
