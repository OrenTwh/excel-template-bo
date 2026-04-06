<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class Farm extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'owner_id',
        'title',
        'phone_number',
        'address_1',
        'address_2',
        'city',
        'state',
        'postcode',
        'size',
        'remarks',
        'status',
    ];

    public function owner() {
        return $this->belongsTo( User::class, 'owner_id' );
    }
    
    public function galleries() {
        return $this->hasMany( FarmGallery::class, 'farm_id' );
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'owner_id',
        'title',
        'phone_number',
        'address_1',
        'address_2',
        'city',
        'state',
        'postcode',
        'size',
        'remarks',
        'status',
    ];

    protected static $logName = 'farms';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} farm";
    }
}
