<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class FieldItemTranslation extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'item_id',
        'locale',
        'name',
        'scientific_name',
        'origin',
        'status',
    ];

    // Relationships
    public function item()
    {
        return $this->belongsTo(FieldItem::class, 'item_id');
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'item_id',
        'locale',
        'name',
        'scientific_name',
        'origin',
        'status',
    ];

    protected static $logName = 'FieldItemTranslation';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} ";
    }
}
