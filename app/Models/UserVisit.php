<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class UserVisit extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'visits';

    protected $fillable = [
        'user_id',
        'visit_date',
        'reference',
        'status',
        'is_recorded',
    ];

    protected $casts = [
        'visit_date' => 'date',
    ];

    public function user() {
        return $this->belongsTo( User::class );
    }

    public function visitDetails() {
        return $this->hasMany( VisitDetail::class, 'visit_id' );
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'user_id',
        'visit_date',
        'reference',
        'status',
        'is_recorded',
    ];

    protected static $logName = 'UserVisit';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} ";
    }
}
