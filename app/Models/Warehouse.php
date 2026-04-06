<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\HasTranslations;

use Helper;

class Warehouse extends Model
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
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'warehouses_products')
                    ->withPivot('quantity', 'price', 'status')
                    ->withTimestamps();
    }

    public function variants()
    {
        return $this->belongsToMany(ProductVariant::class, 'warehouses_variants', 'warehouse_id', 'variant_id')
                    ->withPivot('quantity', 'price', 'status')
                    ->withTimestamps();
    }

    public function bundles()
    {
        return $this->belongsToMany(Bundle::class, 'warehouses_bundles')
                    ->withPivot('quantity', 'price', 'status')
                    ->withTimestamps();
    }

    // Function to calculate total quantity
    public function totalQuantity()
    {
        return $this->products->sum(function ($product) {
            return $product->pivot->quantity;
        });
    }

    // Function to calculate total price
    public function totalPrice()
    {
        return $this->products->sum(function ($product) {
            return $product->pivot->quantity * $product->pivot->price;
        });
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
        'status',
    ];

    protected static $logName = 'warehouses';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} warehouse";
    }
}
