<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ServiceRecordItem extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'service_record_id',
        'type',
        'meta',
    ];

    public function getMetaObjectAttribute() {
        return json_decode( $this->attributes['meta'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'service_record_id',
        'type',
        'meta',
    ];

    protected static $logName = 'service_record_items';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} service record item";
    }
}
