<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\HasTranslations;

use Helper;

class Bundle extends Model
{
    use HasFactory, LogsActivity, HasTranslations;

    protected $fillable = [
        'title',
        'description',
        'image',
        'thumbnail',
        'url_slug',
        'strucuture',
        'sort',
        'promotion_enabled',
        'promotion_start',
        'promotion_end',
        'price',
        'promotion_price',
        'status',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'products_bundles')
        ->withPivot('quantity', 'price');
    }

    public function getImagePathAttribute() {
        return $this->attributes['image'] ? asset( 'storage/' . $this->attributes['image'] ) : asset( 'admin/images/placeholder.png' ) . Helper::assetVersion();
    }

    public function getThumbnailPathAttribute() {
        return $this->attributes['thumbnail'] ? asset( 'storage/'.$this->attributes['thumbnail'] ) : asset( 'admin/images/placeholder.png' ) . Helper::assetVersion();
    }
    
    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    public $translatable = [ 'name', 'description' ];

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'title',
        'description',
        'image',
        'thumbnail',
        'url_slug',
        'strucuture',
        'sort',
        'promotion_enabled',
        'promotion_start',
        'promotion_end',
        'price',
        'promotion_price',
        'status',
    ];

    protected static $logName = 'bundles';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} bundle";
    }
}
