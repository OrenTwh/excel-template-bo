<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\HasTranslations;

use Helper;

class PurchaseMeta extends Model
{
    use HasFactory, LogsActivity, HasTranslations;

    protected $table = 'purchases_metas';

    protected $fillable = [
        'purchase_id',
        'product_id',
        'custom_discount',
        'custom_tax',
        'custom_shipping_cost',
        'quantity',
        'status',

    ];
    
    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    
    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    public $translatable = [ 'name', 'description' ];

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'purchase_id',
        'product_id',
        'custom_discount',
        'custom_tax',
        'custom_shipping_cost',
        'quantity',
        'status',
    ];

    protected static $logName = 'purchase_metas';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} purchase_meta";
    }
}
