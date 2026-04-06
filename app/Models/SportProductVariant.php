<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Helper;

class SportProductVariant extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'sport_product_variants';

    protected $fillable = [
        'sport_product_id',
        'name',
        'sku',
        'price',
        'compare_price',
        'specs',
        'image',
        'sequence',
        'status',
    ];

    protected $casts = [
        'sport_product_id' => 'integer',
        'price'            => 'decimal:2',
        'compare_price'    => 'decimal:2',
        'specs'            => 'array',
        'sequence'         => 'integer',
        'status'           => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(SportProduct::class, 'sport_product_id');
    }

    public function stock()
    {
        return $this->hasOne(SportProductStock::class, 'variant_id');
    }

    public function stockLogs()
    {
        return $this->hasMany(SportProductStockLog::class, 'variant_id')->latest();
    }

    public function getImagePathAttribute()
    {
        return $this->attributes['image']
            ? asset('storage/' . $this->attributes['image'])
            : null;
    }

    public function getEncryptedIdAttribute()
    {
        return Helper::encode($this->attributes['id']);
    }

    public function getAvailableStockAttribute(): int
    {
        $stock = $this->stock;
        if (!$stock) return 0;
        return max(0, $stock->quantity - $stock->reserved_quantity);
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->timezone('Asia/Kuala_Lumpur')->format('Y-m-d H:i:s');
    }

    protected static $logAttributes = ['sport_product_id', 'name', 'sku', 'price', 'compare_price', 'specs', 'sequence', 'status'];
    protected static $logName = 'sport_product_variants';
    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "{$eventName} sport product variant";
    }
}
