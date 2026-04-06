<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class MerchandiseOrderItem extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'merchandise_order_id',
        'merchandise_id',
        'qty',
        'price',
        'subtotal',
        'status'
    ];

    public function order()
    {
        return $this->belongsTo( MerchandiseOrder::class, 'merchandise_order_id' );
    }

    public function merchandise()
    {
        return $this->belongsTo( Merchandise::class );
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'merchandise_order_id',
        'merchandise_id',
        'qty',
        'price',
        'subtotal',
        'status'
    ];

    protected static $logName = 'MerchandiseOrderItem';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} ";
    }
}
