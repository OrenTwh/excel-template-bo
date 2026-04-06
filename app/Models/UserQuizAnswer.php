<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class UserQuizAnswer extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_quiz_id',
        'field_item_group_id',
        'question_type',
        'user_selection',
        'quiz_locale',
        'selected_group_ids',
        'is_correct',
        'points',
        'status',
    ];

    protected $casts = [
        'user_selection' => 'array',
        'selected_group_ids' => 'array',
        'is_correct' => 'boolean',
    ];

    // Relationships
    public function userQuiz()
    {
        return $this->belongsTo(UserQuiz::class, 'user_quiz_id');
    }

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
        'user_quiz_id',
        'field_item_group_id',
        'question_type',
        'user_selection',
        'quiz_locale',
        'selected_group_ids',
        'is_correct',
        'points',
        'status',
    ];

    protected static $logName = 'UserQuizAnswer';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} ";
    }
}
