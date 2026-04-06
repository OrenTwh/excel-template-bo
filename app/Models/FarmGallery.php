<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class FarmGallery extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'farm_id',
        'title',
        'file',
        'type',
        'file_type',
    ];

    public function owner() {
        return $this->belongsTo( Farm::class, 'farm_id' );
    }

    public function getPathAttribute() {
        return $this->attributes['file'] ? asset( 'storage/' . $this->attributes['file'] ) : null;
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'farm_id',
        'title',
        'file',
        'type',
        'file_type',
    ];

    protected static $logName = 'farm_galleries';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} farm gallery";
    }
}
