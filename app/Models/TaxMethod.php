<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\HasTranslations;

use Helper;

class TaxMethod extends Model
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
        'status',
        'tax_percentage',
    ];

    // public function products()
    // {
    //     return $this->hasMany(Product::class, 'tax_method_id');
    // }

    public function getImagePathAttribute() {
        return $this->attributes['image'] ? asset( 'storage/' . $this->attributes['image'] ) : asset( 'admin/images/placeholder.png' ) . Helper::assetVersion();
    }

    public function getThumbnailPathAttribute() {
        return $this->attributes['thumbnail'] ? asset( 'storage/'.$this->attributes['thumbnail'] ) : asset( 'admin/images/placeholder.png' ) . Helper::assetVersion();
    }
    
    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    public function getFormattedTaxAttribute() {
        return $this->attributes['tax_percentage'] ? $this->attributes['tax_percentage'] / 100 : 0.06;
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
        'status',
        'tax_percentage',
    ];

    protected static $logName = 'tax_methods';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} tax_method";
    }
}
