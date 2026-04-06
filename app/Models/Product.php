<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\HasTranslations;

use Helper;

class Product extends Model
{
    use HasFactory, LogsActivity, HasTranslations;

    protected $fillable = [
        'code',
        'title',
        'description',
        'price',
        'image',
        'discount_price',
        'default_froyo_quantity',
        'default_syrup_quantity',
        'default_topping_quantity',
        'status',
        'free_froyo_quantity',
        'free_syrup_quantity',
        'free_topping_quantity',
        'product_type',
    ];

    public function getImagePathAttribute() {
        return $this->attributes['image'] ? asset( 'storage/' . $this->attributes['image'] ) : asset( 'admin/images/placeholder.png' ) . Helper::assetVersion();
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    public $translatable = [ 'title', 'description' ];

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'code',
        'title',
        'description',
        'price',
        'image',
        'discount_price',
        'default_froyo_quantity',
        'default_syrup_quantity',
        'default_topping_quantity',
        'status',
        'free_froyo_quantity',
        'free_syrup_quantity',
        'free_topping_quantity',
        'product_type',
    ];

    protected static $logName = 'categories';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} category";
    }
}
