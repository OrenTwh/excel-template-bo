<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class AdjustmentMeta extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'adjustment_id',
        'product_id',
        'variant_id',
        'bundle_id',
        'amount',
        'original_amount',
        'final_amount',
        'status',
    ];

    public function variant() {
        return $this->belongsTo( ProductVariant::class, 'variant_id' );
    }

    public function product() {
        return $this->belongsTo( Product::class, 'product_id' );
    }

    public function bundle() {
        return $this->belongsTo( Bundle::class, 'bundle_id' );
    }

    public function Adjustment()
    {
        return $this->belongsToMany(Adjustment::class, 'adjustment_id');
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
        'adjustment_id',
        'product_id',
        'variant_id',
        'bundle_id',
        'amount',
        'original_amount',
        'final_amount',
        'status',
    ];

    protected static $logName = 'adjustment_metas';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} adjustment meta";
    }
}
