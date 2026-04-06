<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class ProductVariant extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'product_id',
        'image',
        'title',
        'description',
        'price',
        'sku',
        'quantity',
        'status',
    ];

    public function product() {
        return $this->belongsTo( Product::class, 'product_id' );
    }

    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'warehouses_variants', 'variant_id', 'warehouse_id')
                    ->withPivot('quantity', 'price', 'status')
                    ->withTimestamps();
    }

    public function getPathAttribute() {
        return $this->attributes['image'] ? asset( 'storage/' . $this->attributes['image'] ) : null;
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'product_id',
        'image',
        'title',
        'description',
        'price',
        'sku',
        'quantity',
        'status',
    ];

    protected static $logName = 'product_variants';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} product variant";
    }
}
