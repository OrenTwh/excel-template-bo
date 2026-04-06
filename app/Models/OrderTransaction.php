<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class OrderTransaction extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'order_id',
        'checkout_id',
        'checkout_url',
        'transaction_id',
        'layout_version',
        'redirect_url',
        'notify_url',
        'order_no',
        'order_title',
        'order_detail',
        'amount',
        'currency',
        'transaction_type',
        'status',
        'payment_url',
    ];

    public function order() {
        return $this->belongsTo( Product::class, 'order_id' );
    }
    
    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'order_id',
        'checkout_id',
        'checkout_url',
        'transaction_id',
        'layout_version',
        'redirect_url',
        'notify_url',
        'order_no',
        'order_title',
        'order_detail',
        'amount',
        'currency',
        'transaction_type',
        'status',
        'payment_url',
    ];

    protected static $logName = 'orders';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} order";
    }
}
