<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

class VenueSport extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'venue_sports';

    protected $fillable = [
        'venue_id',
        'sport_id',
        'pricing_method',
        'slot_duration',
        'price_per_slot',
        'price_per_person',
        'price_per_night',
        'open_time',
        'close_time',
        'operating_days',
        'status',
    ];

    protected $casts = [
        'venue_id'         => 'integer',
        'sport_id'         => 'integer',
        'slot_duration'    => 'integer',
        'price_per_slot'   => 'decimal:2',
        'price_per_person' => 'decimal:2',
        'price_per_night'  => 'decimal:2',
        'operating_days'   => 'array',
        'status'           => 'integer',
    ];

    public function venue()
    {
        return $this->belongsTo( Venue::class );
    }

    public function sport()
    {
        return $this->belongsTo( Sport::class );
    }

    public function courts()
    {
        return $this->hasMany( Court::class, 'venue_sport_id' );
    }

    public function getEncryptedIdAttribute()
    {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date )
    {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'venue_id', 'sport_id', 'pricing_method', 'slot_duration', 'price_per_slot',
        'price_per_person', 'price_per_night',
        'open_time', 'close_time', 'operating_days', 'status',
    ];

    protected static $logName = 'VenueSport';
    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string
    {
        return "{$eventName} venue sport";
    }
}
