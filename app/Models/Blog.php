<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;
use Carbon\Carbon;

class Blog extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'meta_title',
        'meta_desc',
        'publish_date',
        'type',
        'slug',
        'multi_lang_image',
        'multi_lang_description',
        'multi_lang_title',
        'multi_lang_subtitle',
    ];

    protected $casts = [
        'publish_date' => 'datetime',
    ];

    /* =========================
     * Relationships
     * ========================= */
    public function tags()
    {
        return $this->hasMany( BlogTag::class );
    }

    public function galleries()
    {
        return $this->hasMany( BlogGallery::class );
    }

    /* =========================
     * Accessors
     * ========================= */
    public function getEncryptedIdAttribute()
    {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date )
    {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    /* =========================
     * Activity Log
     * ========================= */
    protected static $logAttributes = [
        'meta_title',
        'meta_desc',
        'publish_date',
        'type',
        'slug',
        'multi_lang_image',
        'multi_lang_description',
        'multi_lang_title',
        'multi_lang_subtitle',
    ];

    protected static $logName = 'Blog';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string
    {
        return "{$eventName} Blog";
    }
}
