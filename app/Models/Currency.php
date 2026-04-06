<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class Currency extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'code',
        'symbol',
        'name',
        'exchange_rate_to_myr',
        'decimal_places',
        'status',
    ];

    protected $casts = [
        'name' => 'array',
        'exchange_rate_to_myr' => 'decimal:4',
        'decimal_places' => 'integer',
    ];

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    /**
     * Get the currency name in the current locale
     */
    public function getLocalizedNameAttribute()
    {
        $locale = app()->getLocale();
        return $this->name[$locale] ?? $this->name['en'] ?? $this->code;
    }

    /**
     * Format a price with this currency
     */
    public function formatPrice($amount)
    {
        return $this->symbol . ' ' . number_format($amount, $this->decimal_places);
    }

    /**
     * Convert amount from MYR to this currency
     */
    public function convertFromMYR($amountInMYR)
    {
        return $amountInMYR / $this->exchange_rate_to_myr;
    }

    /**
     * Convert amount from this currency to MYR
     */
    public function convertToMYR($amount)
    {
        return $amount * $this->exchange_rate_to_myr;
    }

    /**
     * Scope to get only active currencies
     */
    public function scopeActive($query)
    {
        return $query->where('status', 10);
    }

    /**
     * Get all project prices with this currency
     */
    public function projectPrices()
    {
        return $this->hasMany(\App\Models\ProjectPrice::class, 'currency_code', 'code');
    }

    /**
     * Get all property prices with this currency
     */
    public function propertyPrices()
    {
        return $this->hasMany(\App\Models\PropertyPrice::class, 'currency_code', 'code');
    }

    /**
     * Get all property unit prices with this currency
     */
    public function propertyUnitPrices()
    {
        return $this->hasMany(\App\Models\PropertyUnitPrice::class, 'currency_code', 'code');
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'code',
        'symbol',
        'name',
        'exchange_rate_to_myr',
        'decimal_places',
        'status',
    ];

    protected static $logName = 'Currency';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} currency {$this->code}";
    }
}
