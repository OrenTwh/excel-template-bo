<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'photo',
        'name',
        'email',
        'phone_number',
        'identification_number',
        'date_of_birth',
        'remarks',
        'status',
    ];

    public function getPathAttribute() {
        return $this->attributes['photo'] ? asset( 'storage/' . $this->attributes['photo'] ) : null;
    }

    public function getLocalDateOfBirthAttribute() {
        return $this->attributes['date_of_birth'] ? Carbon::createFromFormat( 'Y-m-d', $this->attributes['date_of_birth'] )->setTimezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' ) : '-';
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
        'identification_number',
        'date_of_birth',
        'remarks',
        'status',
    ];

    protected static $logName = 'employees';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} employee";
    }
}
