<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class MerchandiseOrder extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'order_no',
        'total_price',
        'status'
    ];

    public function items()
    {
        return $this->hasMany( MerchandiseOrderItem::class );
    }

    public function merchandises()
    {
        return $this->belongsToMany( Merchandise::class, 'merchandise_order_items' )
                    ->withPivot( 'qty', 'price', 'subtotal' );
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'order_no',
        'total_price',
        'status'
    ];

    protected static $logName = 'MerchandiseOrder';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} ";
    }
}
