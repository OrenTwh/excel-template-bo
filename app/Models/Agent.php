<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class Agent extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'nickname',
        'profile_picture',
        'serial_number',
        'calling_code',
        'phone_number',
        'whatsapp_link',
        'facebook_link',
        'telegram_link',
        'instagram_link',
        'property_sold',
        'email',
        'status',
    ];

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    public function getProfilePicturePathAttribute() {
        return $this->attributes['profile_picture'] ? asset( 'storage/' . $this->attributes['profile_picture'] ) : asset( 'admin/images/profile_image.png' ) . Helper::assetVersion();
    }

    public function getProfilePicturePathNewAttribute() {
        return $this->attributes['profile_picture'] ? asset( 'storage/' . $this->attributes['profile_picture'] ) : asset( 'admin/images/profile_image.png' ) . Helper::assetVersion();
    }
    
    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'name',
        'nickname',
        'profile_picture',
        'serial_number',
        'calling_code',
        'phone_number',
        'whatsapp_link',
        'facebook_link',
        'telegram_link',
        'instagram_link',
        'property_sold',
        'email',
        'status',
    ];

    protected static $logName = 'Agent';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} agents";
    }
}
