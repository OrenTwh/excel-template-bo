<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class Sport extends Model
{
    use HasFactory, LogsActivity;

    // Sport types
    const TYPE_COURT         = 'court';
    const TYPE_ACTIVITY      = 'activity';
    const TYPE_WATER         = 'water';
    const TYPE_ACCOMMODATION = 'accommodation';

    // Pricing methods
    const PRICING_PER_SLOT   = 'per_slot';
    const PRICING_PER_PERSON = 'per_person';
    const PRICING_PER_HOUR   = 'per_hour';
    const PRICING_PER_NIGHT  = 'per_night';

    public static function typeOptions(): array
    {
        return [
            self::TYPE_COURT         => 'Court / Time Slot',
            self::TYPE_ACTIVITY      => 'Activity / Session',
            self::TYPE_WATER         => 'Water Activity',
            self::TYPE_ACCOMMODATION => 'Accommodation',
        ];
    }

    public static function pricingMethodOptions(): array
    {
        return [
            self::PRICING_PER_SLOT   => 'Per Slot (fixed block)',
            self::PRICING_PER_PERSON => 'Per Person (headcount × rate)',
            self::PRICING_PER_HOUR   => 'Per Hour (duration × hourly rate)',
            self::PRICING_PER_NIGHT  => 'Per Night',
        ];
    }

    protected $fillable = [
        'name',
        'slug',
        'type',
        'pricing_method',
        'description',
        'icon',
        'image',
        'thumbnail',
        'min_players',
        'max_players',
        'active',
        'sequence',
        'status',
    ];

    protected $casts = [
        'min_players' => 'integer',
        'max_players' => 'integer',
        'active'      => 'boolean',
        'sequence'    => 'integer',
        'status'      => 'integer',
    ];

    public function courts()
    {
        return $this->belongsToMany(Court::class, 'court_sport');
    }

    public function tags()
    {
        return $this->belongsToMany(SportsTag::class, 'sport_sports_tag');
    }

    public function getEncryptedIdAttribute()
    {
        return Helper::encode($this->attributes['id']);
    }

    public function getIconPathAttribute()
    {
        return $this->attributes['icon'] ? asset('storage/' . $this->attributes['icon']) : asset('admin/images/placeholder.png') . Helper::assetVersion();
    }

    public function getImagePathAttribute()
    {
        return !empty($this->attributes['image']) ? asset('storage/' . $this->attributes['image']) : null;
    }

    public function getThumbnailPathAttribute()
    {
        return !empty($this->attributes['thumbnail']) ? asset('storage/' . $this->attributes['thumbnail']) : null;
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->timezone('Asia/Kuala_Lumpur')->format('Y-m-d H:i:s');
    }

    protected static $logAttributes = [
        'name',
        'slug',
        'description',
        'icon',
        'image',
        'thumbnail',
        'min_players',
        'max_players',
        'active',
        'status',
    ];

    protected static $logName = 'Sport';

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
