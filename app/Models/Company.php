<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class Company extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'registration_no',
        'email',
        'phone_number',
        'address',
        'bank_name',
        'account_no',
        'status',
    ];

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'name',
        'registration_no',
        'email',
        'phone_number',
        'address',
        'bank_name',
        'account_no',
        'status',
    ];

    protected static $logName = 'companies';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} company";
    }
}
