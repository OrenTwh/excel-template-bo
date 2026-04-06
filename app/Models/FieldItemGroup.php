<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class FieldItemGroup extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'field_item_id',
        'group_type',
        'position',
        'status',
    ];

    // Relationships
    public function fieldItem()
    {
        return $this->belongsTo(FieldItem::class, 'field_item_id');
    }

    public function translations()
    {
        return $this->hasMany(FieldItemGroupTranslation::class, 'field_item_group_id');
    }

    public function images()
    {
        return $this->hasMany(FieldItemGroupImage::class, 'field_item_group_id');
    }

    public function quizAnswers()
    {
        return $this->hasMany(UserQuizAnswer::class, 'field_item_group_id');
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'field_item_id',
        'group_type',
        'position',
        'status',
    ];

    protected static $logName = 'FieldItemGroup';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} ";
    }
}
