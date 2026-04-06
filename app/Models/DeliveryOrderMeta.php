<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\HasTranslations;

use Helper;

class DeliveryOrderMeta extends Model
{
    use HasFactory, LogsActivity, HasTranslations;

    protected $table = 'delivery_orders_metas';

    protected $fillable = [
        'delivery_order_id',
        'product_id',
        'variant_id',
        'bundle_id',
        'custom_discount',
        'custom_tax',
        'custom_shipping_cost',
        'quantity',
        'status',

    ];
    
    public function deliveryOrder()
    {
        return $this->belongsTo(SalesOrder::class, 'delivery_order_id');
    }
    
    public function quotation()
    {
        return $this->belongsTo(Quotation::class, 'quotation_id');
    }

    public function variant() {
        return $this->belongsTo( ProductVariant::class, 'variant_id' );
    }

    public function product() {
        return $this->belongsTo( Product::class, 'product_id' );
    }

    public function bundle() {
        return $this->belongsTo( Bundle::class, 'bundle_id' );
    }
    
    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    public $translatable = [ 'name', 'description' ];

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'delivery_order_id',
        'product_id',
        'variant_id',
        'bundle_id',
        'custom_discount',
        'custom_tax',
        'custom_shipping_cost',
        'quantity',
        'status',
    ];

    protected static $logName = 'delivery_order_metas';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} delivery_order_meta";
    }
}
