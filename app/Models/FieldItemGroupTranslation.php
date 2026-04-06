<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class FieldItemGroupTranslation extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'field_item_group_id',
        'locale',
        'text',
        'status',
    ];

    // Relationships
    public function fieldItemGroup()
    {
        return $this->belongsTo(FieldItemGroup::class, 'field_item_group_id');
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'field_item_group_id',
        'locale',
        'text',
        'status',
    ];

    protected static $logName = 'FieldItemGroupTranslation';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} ";
    }
}
