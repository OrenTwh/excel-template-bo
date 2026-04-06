<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class Court extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'venue_sport_id',
        'name',
        'slug',
        'description',
        'capacity',
        'price_per_hour',
        'image',
        'active',
        'status',
    ];

    protected $casts = [
        'venue_sport_id' => 'integer',
        'capacity'       => 'integer',
        'price_per_hour' => 'decimal:2',
        'active'         => 'boolean',
        'status'         => 'integer',
    ];

    public function venueSport()
    {
        return $this->belongsTo( VenueSport::class, 'venue_sport_id' );
    }

    public function venue()
    {
        return $this->hasOneThrough( Venue::class, VenueSport::class, 'id', 'id', 'venue_sport_id', 'venue_id' );
    }

    public function sport()
    {
        return $this->hasOneThrough( Sport::class, VenueSport::class, 'id', 'id', 'venue_sport_id', 'sport_id' );
    }

    public function bookings()
    {
        return $this->hasMany(CourtBooking::class);
    }

    public function calendars()
    {
        return $this->hasMany(CourtCalendar::class);
    }

    public function galleries()
    {
        return $this->hasMany(CourtGallery::class);
    }

    public function pricings()
    {
        return $this->hasMany(CourtPricing::class)->orderBy('id');
    }

    /**
     * Resolve the effective price per slot for a given time window.
     * Tiers are checked in creation order; first matching tier wins.
     * Falls back to price_per_hour when no tier matches.
     *
     * @param  string  $startTime  H:i format
     * @param  string  $endTime    H:i format
     * @param  int     $numCourts  courts in the booking group (for min_courts tiers)
     */
    public function resolvePrice(string $startTime, string $endTime, int $numCourts = 1): float
    {
        foreach ($this->pricings as $tier) {
            if ($numCourts < $tier->min_courts) {
                continue;
            }

            if ($tier->time_from !== null && $tier->time_to !== null) {
                // Booking window must overlap tier window
                if (strcmp($startTime, $tier->time_to) < 0 && strcmp($endTime, $tier->time_from) > 0) {
                    return (float) $tier->price;
                }
            } else {
                // No time restriction — applies all day
                return (float) $tier->price;
            }
        }

        return (float) $this->price_per_hour;
    }

    public function getEncryptedIdAttribute()
    {
        return Helper::encode($this->attributes['id']);
    }

    public function getImagePathAttribute()
    {
        return $this->attributes['image'] ? asset('storage/' . $this->attributes['image']) : asset('admin/images/placeholder.png') . Helper::assetVersion();
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->timezone('Asia/Kuala_Lumpur')->format('Y-m-d H:i:s');
    }

    protected static $logAttributes = [
        'venue_sport_id',
        'name',
        'slug',
        'description',
        'capacity',
        'price_per_hour',
        'image',
        'active',
        'status',
    ];

    protected static $logName = 'Court';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "{$eventName} ";
    }
}
