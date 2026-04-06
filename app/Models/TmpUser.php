<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class TmpUser extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'country_id',
        'phone_number',
        'calling_code',
        'email',
        'otp_code',
        'status',
        'expire_on',
    ];

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'country_id',
        'phone_number',
        'calling_code',
        'otp_code',
        'email',
        'status',
        'expire_on',
    ];

    protected static $logName = 'tmp_users';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} tmp user";
    }
}
