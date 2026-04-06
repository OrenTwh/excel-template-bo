<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class FieldActivityOrderDetail extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'field_activity_order_id',
        'field_activity_id',
        'qty',
        'price',
        'subtotal',
        'status',
    ];

    protected $casts = [
        'qty'      => 'integer',
        'price'    => 'decimal:2',
        'subtotal' => 'decimal:2',
        'status'   => 'integer',
    ];

    /* Relationships */
    public function order()
    {
        return $this->belongsTo( FieldActivityOrder::class, 'field_activity_order_id' );
    }

    public function activity()
    {
        return $this->belongsTo( FieldActivity::class, 'field_activity_id' );
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'field_activity_order_id',
        'field_activity_id',
        'qty',
        'price',
        'subtotal',
        'status',
    ];

    protected static $logName = 'FieldActivityOrderDetail';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} ";
    }
}
