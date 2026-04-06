<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class CmsArticleBanner extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'cms_article_id',
        'image',
        'alt_text',
        'sort_order',
        'target_page',
        'target_page_web',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    public function getImagePathAttribute() {
        return $this->attributes['image'] ? asset( 'storage/'.$this->attributes['image'] ) : asset( 'admin/images/placeholder.png' ) . Helper::assetVersion();
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
        'image',
        'alt_text',
        'sort_order',
        'target_page',
        'target_page_web',
    ];

    protected static $logName = 'CmsArticleBanner';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} ";
    }
}
