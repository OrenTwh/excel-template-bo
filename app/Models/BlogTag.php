<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

class BlogTag extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'blog_id',
        'tag',
    ];

    /* =========================
     * Relationships
     * ========================= */
    public function blog()
    {
        return $this->belongsTo( Blog::class );
    }

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
        'blog_id',
        'tag',
    ];

    protected static $logName = 'BlogTag';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string
    {
        return "{$eventName} BlogTag";
    }
}
