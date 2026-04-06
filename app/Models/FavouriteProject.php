<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class FavouriteProject extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'project_id',
        'user_id',
        'status',
        'favourite_type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
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

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'project_id',
        'user_id',
        'status',
        'favourite_type',
    ];

    protected static $logName = 'FavouriteProject';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} favourite project";
    }
}