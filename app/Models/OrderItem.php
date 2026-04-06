<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class OrderItem extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'order_id',
        'grade',
        'weight',
        'rate',
        'subtotal',
        'total',
        'status',
    ];

    public function order() {
        return $this->belongsTo( Order::class, 'order_id' );
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'order_id',
        'grade',
        'weight',
        'rate',
        'subtotal',
        'total',
        'status',
    ];

    protected static $logName = 'order_items';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} order item";
    }
}
