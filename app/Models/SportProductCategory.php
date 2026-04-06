<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\HasTranslations;
use Helper;

class SportProductCategory extends Model
{
    use HasFactory, LogsActivity, HasTranslations;

    protected $table = 'sport_product_categories';

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'image',
        'sequence',
        'status',
    ];

    protected $casts = [
        'parent_id' => 'integer',
        'sequence'  => 'integer',
        'status'    => 'integer',
    ];

    public $translatable = ['name'];

    public function parent()
    {
        return $this->belongsTo(SportProductCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(SportProductCategory::class, 'parent_id');
    }

    public function products()
    {
        return $this->hasMany(SportProduct::class, 'category_id');
    }

    public function getImagePathAttribute()
    {
        return $this->attributes['image']
            ? asset('storage/' . $this->attributes['image'])
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

    protected static $logAttributes = ['parent_id', 'name', 'slug', 'image', 'sequence', 'status'];
    protected static $logName = 'sport_product_categories';
    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "{$eventName} sport product category";
    }
}
