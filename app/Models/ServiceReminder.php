<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class ServiceReminder extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'service_id',
        'vehicle_id',
        'remarks',
        'service_date',
        'due_date',
    ];

    public function service() {
        return $this->belongsTo( Service::class, 'service_id' );
    }

    public function vehicle() {
        return $this->belongsTo( Vehicle::class, 'vehicle_id' );
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'service_id',
        'vehicle_id',
        'remarks',
        'service_date',
        'due_date',
    ];

    protected static $logName = 'service_reminders';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} service reminder";
    }
}
