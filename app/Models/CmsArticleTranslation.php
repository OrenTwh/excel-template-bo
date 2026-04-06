<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class CmsArticleTranslation extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'cms_article_id',
        'locale',
        'title',
        'slug',
        'short_description',
        'description',
        'meta_title',
        'meta_description',
    ];

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    // Relationships
    public function article()
    {
        return $this->belongsTo(CmsArticle::class, 'cms_article_id');
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'cms_article_id',
        'locale',
        'title',
        'slug',
        'short_description',
        'description',
        'meta_title',
        'meta_description',
    ];

    protected static $logName = 'CmsArticleTranslation';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} ";
    }
}
