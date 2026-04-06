<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class CapacityOverride extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'date',
        'ticket_type_id',
        'capacity',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'date',
        'ticket_type_id',
        'capacity',
        'status',
    ];

    protected static $logName = 'CapacityOverride';

    public function ticketType() {
        return $this->belongsTo(TicketType::class);
    }

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} ";
    }
}
