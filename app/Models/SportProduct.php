<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\HasTranslations;
use Helper;

class SportProduct extends Model
{
    use HasFactory, LogsActivity, HasTranslations;

    protected $table = 'sport_products';

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'images',
        'sequence',
        'status',
    ];

    protected $casts = [
        'category_id' => 'integer',
        'images'      => 'array',
        'sequence'    => 'integer',
        'status'      => 'integer',
    ];

    public $translatable = ['name', 'description'];

    public function category()
    {
        return $this->belongsTo(SportProductCategory::class, 'category_id');
    }

    public function sports()
    {
        return $this->belongsToMany(Sport::class, 'sport_product_sport', 'sport_product_id', 'sport_id');
    }

    public function variants()
    {
        return $this->hasMany(SportProductVariant::class, 'sport_product_id')->orderBy('sequence');
    }

    public function activeVariants()
    {
        return $this->hasMany(SportProductVariant::class, 'sport_product_id')
            ->where('status', 10)
            ->orderBy('sequence');
    }

    public function getImagePathsAttribute(): array
    {
        $images = $this->images ?? [];
        return array_map(fn($img) => asset('storage/' . $img), $images);
    }

    public function getFirstImagePathAttribute(): string
    {
        $images = $this->images ?? [];
        return count($images)
            ? asset('storage/' . $images[0])
            : asset('admin/images/placeholder.png') . Helper::assetVersion();
    }

    public function getEncryptedIdAttribute()
    {
        return Helper::encode($this->attributes['id']);
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->timezone('Asia/Kuala_Lumpur')->format('Y-m-d H:i:s');
    }

    protected static $logAttributes = ['category_id', 'name', 'slug', 'description', 'images', 'sequence', 'status'];
    protected static $logName = 'sport_products';
    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "{$eventName} sport product";
    }
}
