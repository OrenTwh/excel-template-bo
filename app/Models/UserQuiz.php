<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class UserQuiz extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'field_item_id',
        'field_item_group_id',
        'visit_id',
        'is_completed',
        'score',
        'attempts',
        'completed_at',
        'status',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function fieldItem()
    {
        return $this->belongsTo(FieldItem::class, 'field_item_id');
    }

    public function fieldItemGroup()
    {
        return $this->belongsTo(FieldItemGroup::class, 'field_item_group_id');
    }

    public function visit()
    {
        return $this->belongsTo(Visit::class, 'visit_id');
    }

    public function answers()
    {
        return $this->hasMany(UserQuizAnswer::class, 'user_quiz_id');
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'user_id',
        'field_item_id',
        'field_item_group_id',
        'visit_id',
        'is_completed',
        'score',
        'attempts',
        'completed_at',
        'status',
    ];

    protected static $logName = 'UserQuiz';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} ";
    }
}
