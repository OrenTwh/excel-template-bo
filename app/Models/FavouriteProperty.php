<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class FavouriteProperty extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'property_id',
        'user_id',
        'favourite_type',
        'status',
    ];

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
    
    public function getFavouriteTypeLabelAttribute()
    {
        $favouriteTypes = [
            '1' => __( 'favourite.buy' ),
            '2' => __( 'favourite.long_rent' ),
            '3' => __( 'favourite.short_rent' ),
        ];

        return $favouriteTypes[$this->attributes['favourite_type']] ?? null;
    }

    protected static $logAttributes = [
        'property_id',
        'favourite_type',
        'user_id',
        'status',
    ];

    protected static $logName = 'FavouriteProperty';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} ";
    }
}
