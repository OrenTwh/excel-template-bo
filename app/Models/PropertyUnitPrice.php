<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

class PropertyUnitPrice extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'property_unit_id',
        'currency_code',
        'built_up_area',
        'selling_price',
        'rental_price',
        'rental_price_per_night',
        'maintenance_fee',
        'status',
        'rental_price',
        'rental_price_per_night',
        'min_build_up_area_psf',
        'min_selling_price_psf',
        'min_selling_price_unit',
        'min_maintenance_fee_psf',
    ];

    protected $casts = [
        'built_up_area' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'rental_price' => 'decimal:2',
        'rental_price_per_night' => 'decimal:2',
        'maintenance_fee' => 'decimal:2',
        'min_build_up_area_psf' => 'decimal:2',
        'min_selling_price_psf' => 'decimal:2',
        'min_selling_price_unit' => 'decimal:2',
        'min_maintenance_fee_psf' => 'decimal:2',
    ];

    public function propertyUnit()
    {
        return $this->belongsTo(PropertyUnit::class, 'property_unit_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    /**
     * Scope to get only active prices
     */
    public function scopeActive($query)
    {
        return $query->where('status', 10);
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'property_unit_id',
        'currency_code',
        'built_up_area',
        'selling_price',
        'rental_price',
        'rental_price_per_night',
        'maintenance_fee',
        'status',
        'rental_price',
        'rental_price_per_night',
        'min_build_up_area_psf',
        'min_selling_price_psf',
        'min_selling_price_unit',
        'min_maintenance_fee_psf',
    ];

    protected static $logName = 'PropertyUnitPrice';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} property unit price for currency {$this->currency_code}";
    }
}
