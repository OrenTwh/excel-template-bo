<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class CmsArticle extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'thumbnail',
        'publish_date',
        'status',
    ];

    protected $casts = [
        'publish_date' => 'date',
        'status' => 'integer',
    ];

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    public function getThumbnailPathAttribute() {
        return $this->attributes['thumbnail'] ? asset( 'storage/'.$this->attributes['thumbnail'] ) : asset( 'admin/images/placeholder.png' ) . Helper::assetVersion();
    }

    // Relationships
    public function translations()
    {
        return $this->hasMany(CmsArticleTranslation::class);
    }

    public function banners()
    {
        return $this->hasMany(CmsArticleBanner::class)->orderBy('sort_order');
    }

    public function tags()
    {
        return $this->belongsToMany(CmsTag::class, 'cms_article_tag');
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'thumbnail',
        'publish_date',
        'status',
    ];

    protected static $logName = 'CmsArticle';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} ";
    }
}
