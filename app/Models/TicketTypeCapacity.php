<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class TicketTypeCapacity extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'ticket_type_id',
        'weekday_capacity',
        'weekend_capacity',
        'status',
    ];

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'ticket_type_id',
        'weekday_capacity',
        'weekend_capacity',
        'status',
    ];

    protected static $logName = 'TicketTypeCapacity';

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

    public function getCapacity( $date = null ) {

        $date = $date ? Carbon::parse( $date ) : Carbon::now();
    
        $isWeekend = $date->isWeekend();
    
        return $isWeekend
            ? ( ( int ) $this->weekend_capacity )
            : ( ( int ) $this->weekday_capacity );
    }

    public function getCapacityAttribute()
    {
        $today = Carbon::now()->timezone( 'Asia/Kuala_Lumpur' );
        $isWeekend = $today->isWeekend();

        return $isWeekend
            ? $this->attributes['weekend_capacity']
            : $this->attributes['weekday_capacity'];
    }

}
