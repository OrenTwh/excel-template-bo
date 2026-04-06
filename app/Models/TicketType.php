<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class TicketType extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'nationality',
        'price',
        'active',
        'status',
    ];

    protected $casts = [
        'active' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'name',
        'nationality',
        'price',
        'active',
        'status',
    ];

    protected static $logName = 'TicketType';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} ";
    }

    public function capacity() {
        return $this->hasOne(TicketTypeCapacity::class);
    }

    public function capacityOverrides() {
        return $this->hasMany(CapacityOverride::class);
    }
}
