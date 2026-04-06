<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class FieldActivityOrder extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'visit_id',
        'order_no',
        'total_price',
        'status',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'status'      => 'integer',
    ];

    /* Relationships */
    public function visit()
    {
        return $this->belongsTo( Visit::class, 'visit_id' );
    }

    public function details()
    {
        return $this->hasMany( FieldActivityOrderDetail::class, 'field_activity_order_id' );
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'visit_id',
        'order_no',
        'total_price',
        'status',
    ];

    protected static $logName = 'FieldActivityOrder';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} ";
    }
}
