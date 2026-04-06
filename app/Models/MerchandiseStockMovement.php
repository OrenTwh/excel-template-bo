<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class MerchandiseStockMovement extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'merchandise_id',
        'type',
        'quantity',
        'remark',
        'before_quantity',
        'after_quantity',
        'status'
    ];

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
        'merchandise_id',
        'type',
        'quantity',
        'remark',
        'before_quantity',
        'after_quantity',
        'status'
    ];

    protected static $logName = 'MerchandiseStockMovement';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} ";
    }
}
