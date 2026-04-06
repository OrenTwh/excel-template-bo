<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

class Venue extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'about_us',
        'amenities',
        'opening_hours',
        'opening_hours_pricing',
        'venue_layout',
        'venue_policy',
        'gmap_link',
        'waze_link',
        'calling_code',
        'phone_number',
        'whatsapp_link',
        'address_1',
        'address_2',
        'city',
        'state',
        'postcode',
        'latitude',
        'longitude',
        'image',
        'status',
    ];

    protected $casts = [
        'amenities'     => 'array',
        'opening_hours' => 'array',
        'latitude'      => 'decimal:7',
        'longitude'     => 'decimal:7',
        'status'        => 'integer',
    ];

    public function venueSports()
    {
        return $this->hasMany( VenueSport::class );
    }

    public function sports()
    {
        return $this->belongsToMany( Sport::class, 'venue_sports' );
    }

    public function courts()
    {
        return $this->hasManyThrough( Court::class, VenueSport::class, 'venue_id', 'venue_sport_id' );
    }

    public function getEncryptedIdAttribute()
    {
        return Helper::encode( $this->attributes['id'] );
    }

    public function getImagePathAttribute()
    {
        return $this->attributes['image']
            ? asset( 'storage/' . $this->attributes['image'] )
            : asset( 'admin/images/placeholder.png' ) . Helper::assetVersion();
    }

    public function getVenueLayoutPathAttribute()
    {
        return !empty( $this->attributes['venue_layout'] )
            ? asset( 'storage/' . $this->attributes['venue_layout'] )
            : null;
    }

    protected function serializeDate( DateTimeInterface $date )
    {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'name', 'slug', 'description', 'about_us', 'amenities', 'opening_hours',
        'venue_layout', 'opening_hours_pricing', 'venue_policy', 'gmap_link', 'waze_link', 'calling_code',
        'phone_number', 'whatsapp_link', 'address_1', 'address_2',
        'city', 'state', 'postcode', 'latitude', 'longitude', 'image', 'status',
    ];

    protected static $logName = 'Venue';
    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string
    {
        return "{$eventName} venue";
    }
}
