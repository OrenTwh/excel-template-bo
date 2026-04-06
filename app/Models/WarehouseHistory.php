<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\HasTranslations;

use Helper;

class WarehouseHistory extends Model
{
    use HasFactory, LogsActivity, HasTranslations;

    protected $table = 'warehouse_histories';

    protected $fillable = [
        'warehouse_id',
        'product_id',
        'variant_id',
        'bundle_id',
        'original_quantity',
        'update_quantity',
        'final_quantity',
        'status',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variant()
    {
        return $this->belongsTo(Variant::class, 'variant_id');
    }

    public function bundle()
    {
        return $this->belongsTo(Bundle::class, 'bundle_id');
    }
    
    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    public $translatable = [ 'name', 'description' ];

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'warehouse_id',
        'product_id',
        'variant_id',
        'bundle_id',
        'original_quantity',
        'update_quantity',
        'final_quantity',
        'status',
    ];

    protected static $logName = 'warehouse_histories';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} warehouse_history";
    }
}
