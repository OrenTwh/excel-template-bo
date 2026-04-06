<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\HasTranslations;

use Helper;

class UserNotificationUser extends Model
{
    use HasFactory, LogsActivity, HasTranslations;

    protected $fillable = [
        'user_notification_id',
        'user_id',
    ];

    protected $appends = [
        'encrypted_id',
    ];

    public function user() {
        return $this->belongsTo( User::class, 'user_id' );
    }

    public function userNotification() {
        return $this->belongsTo( UserNotification::class, 'user_notification_id' );
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    public $translatable = [ 'title', 'content' ];

    protected function serializeDate(DateTimeInterface $date) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'user_notification_id',
        'user_id',
    ];

    protected static $logName = 'user_notification_users';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} user notification user";
    }
}
