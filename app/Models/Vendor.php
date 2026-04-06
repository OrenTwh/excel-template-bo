<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

class Vendor extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'photo',
        'name',
        'email',
        'phone_number',
        'address',
        'website',
        'remarks',
        'type',
        'status',
    ];

    public function getPathAttribute() {
        return $this->attributes['photo'] ? asset( 'storage/' . $this->attributes['photo'] ) : null;
    }

    public function getAddressObjectAttribute() {
        return json_decode( $this->attributes['address'] );
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'photo',
        'name',
        'email',
        'phone_number',
        'address',
        'website',
        'remarks',
        'type',
        'status',
    ];

    protected static $logName = 'vendors';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} vendor";
    }
}
